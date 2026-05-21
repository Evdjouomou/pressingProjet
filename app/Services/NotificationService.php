<?php
namespace App\Services;

use CodeIgniter\Email\Email;

class NotificationService
{
    protected $db;
    protected $emailLib;

    public function __construct()
    {
        $this->db       = \Config\Database::connect();
        $this->emailLib = \Config\Services::email();
    }

    // ═══════════════════════════════════════════
    // POINT D'ENTRÉE PRINCIPAL
    // Crée la notification en base et l'envoie
    // ═══════════════════════════════════════════
    public function envoyer(
        int    $clientId,
        string $type,
        string $sujet,
        string $message,
        ?int   $depotId = null,
        array  $canaux  = ['interne', 'email', 'sms']
    ): void {
        // Récupérer les infos du client
        $client = $this->db->table('clients')
                           ->where('id_client', $clientId)
                           ->get()->getRowArray();

        if (!$client) return;

        foreach ($canaux as $canal) {
            $this->creerNotification($clientId, $depotId, $type, $canal, $sujet, $message);

            switch ($canal) {
                case 'email':
                    if (!empty($client['email'])) {
                        $this->envoyerEmail($client['email'], $sujet, $message, $clientId, $depotId, $type);
                    }
                    break;

                case 'sms':
                    if (!empty($client['telephone'])) {
                        $this->envoyerSms($client['telephone'], $message, $clientId, $depotId, $type);
                    }
                    break;

                case 'interne':
                    // Déjà créé en base ci-dessus, rien de plus à faire
                    $this->marquerEnvoye($clientId, $depotId, $type, 'interne');
                    break;
            }
        }
    }

    // ═══════════════════════════════════════════
    // NOTIFICATIONS AUTOMATIQUES
    // ═══════════════════════════════════════════
    public function depotConfirme(int $depotId): void
    {
        $depot  = $this->getDepotAvecClient($depotId);
        if (!$depot) return;

        $sujet   = "Votre dépôt {$depot['code_commande']} a été enregistré";
        $message = $this->templateDepotConfirme($depot);

        $this->envoyer($depot['client_id'], 'depot_confirme', $sujet, $message, $depotId);
    }

    public function commandePrete(int $depotId): void
    {
        $depot  = $this->getDepotAvecClient($depotId);
        if (!$depot) return;

        $sujet   = "✅ Votre commande {$depot['code_commande']} est prête !";
        $message = $this->templateCommandePrete($depot);

        $this->envoyer($depot['client_id'], 'commande_prete', $sujet, $message, $depotId);
    }

    public function retraitConfirme(int $depotId): void
    {
        $depot  = $this->getDepotAvecClient($depotId);
        if (!$depot) return;

        $sujet   = "Merci ! Retrait confirmé — {$depot['code_commande']}";
        $message = $this->templateRetraitConfirme($depot);

        $this->envoyer($depot['client_id'], 'retrait_confirme', $sujet, $message, $depotId);
    }

    // Appelé par une tâche planifiée (cron)
    public function envoyerRappelsNonRetires(int $joursDelai = 3): void
    {
        $depots = $this->db->query("
            SELECT d.*, c.nomclient, c.telephone, c.email, c.id_client
            FROM depots d
            JOIN clients c ON c.id_client = d.client_id
            WHERE d.statut_global = 'pret'
              AND d.date_livraison_prevue < DATE_SUB(NOW(), INTERVAL ? DAY)
              AND d.id_depot NOT IN (
                  SELECT depot_id FROM notifications
                  WHERE type = 'rappel_retrait'
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
                    AND depot_id IS NOT NULL
              )
        ", [$joursDelai])->getResultArray();

        foreach ($depots as $depot) {
            $sujet   = "⏰ Rappel : votre commande {$depot['code_commande']} vous attend";
            $message = $this->templateRappel($depot);
            $this->envoyer($depot['id_client'], 'rappel_retrait', $sujet, $message, $depot['id_depot']);
        }
    }

    // ═══════════════════════════════════════════
    // CAMPAGNES
    // ═══════════════════════════════════════════
    public function lancerCampagne(int $idCampagne): array
    {
        $campagne = $this->db->table('campagnes')
                             ->where('id_campagne', $idCampagne)
                             ->get()->getRowArray();

        if (!$campagne || $campagne['statut'] === 'envoyee') {
            return ['success' => false, 'message' => 'Campagne introuvable ou déjà envoyée.'];
        }

        $clients = $this->getCiblesCampagne($campagne);
        $nbEnvoyes = 0;

        foreach ($clients as $client) {
            $canaux = $campagne['canal'] === 'tous'
                ? ['interne', 'email', 'sms']
                : [$campagne['canal']];

            $this->envoyer(
                $client['id_client'],
                'campagne',
                $campagne['titre'],
                $campagne['message'],
                null,
                $canaux
            );
            $nbEnvoyes++;
        }

        $this->db->table('campagnes')->where('id_campagne', $idCampagne)->update([
            'statut'         => 'envoyee',
            'date_envoi_reel' => date('Y-m-d H:i:s'),
            'nb_envoyes'     => $nbEnvoyes,
        ]);

        return ['success' => true, 'nb_envoyes' => $nbEnvoyes];
    }

    // ═══════════════════════════════════════════
    // ENVOI EMAIL
    // ═══════════════════════════════════════════
    private function envoyerEmail(
        string $to, string $sujet, string $message,
        int $clientId, ?int $depotId, string $type
    ): void {
        try {
            $this->emailLib->clear();
            $this->emailLib->setTo($to);
            $this->emailLib->setSubject($sujet);
            $this->emailLib->setMessage($this->wrapEmailHtml($sujet, $message));

            if ($this->emailLib->send()) {
                $this->marquerEnvoye($clientId, $depotId, $type, 'email');
            } else {
                $this->marquerEchec($clientId, $depotId, $type, 'email', $this->emailLib->printDebugger());
            }
        } catch (\Throwable $e) {
            $this->marquerEchec($clientId, $depotId, $type, 'email', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════
    // ENVOI SMS — Africa's Talking
    // ═══════════════════════════════════════════
    private function envoyerSms(
        string $telephone, string $message,
        int $clientId, ?int $depotId, string $type
    ): void {
        $apiKey   = env('AT_API_KEY');   // Clé Africa's Talking dans .env
        $username = env('AT_USERNAME');  // Username Africa's Talking
        $sender   = env('AT_SENDER');    // Nom affiché (ex: PRESSING)

        // Formater le numéro camerounais → +237XXXXXXXXX
        $numero = preg_replace('/\D/', '', $telephone);
        if (strlen($numero) === 9) {
            $numero = '+237' . $numero;
        } elseif (!str_starts_with($numero, '+')) {
            $numero = '+' . $numero;
        }

        try {
            $ch = curl_init('https://api.africastalking.com/version1/messaging');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'apiKey: ' . $apiKey,
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                CURLOPT_POSTFIELDS => http_build_query([
                    'username' => $username,
                    'to'       => $numero,
                    'message'  => strip_tags($message),
                    'from'     => $sender,
                ]),
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($response, true);
            $ok   = $httpCode === 201
                    && isset($data['SMSMessageData']['Recipients'][0]['status'])
                    && $data['SMSMessageData']['Recipients'][0]['status'] === 'Success';

            if ($ok) {
                $this->marquerEnvoye($clientId, $depotId, $type, 'sms');
            } else {
                $this->marquerEchec($clientId, $depotId, $type, 'sms', $response);
            }
        } catch (\Throwable $e) {
            $this->marquerEchec($clientId, $depotId, $type, 'sms', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════
    // TEMPLATES DE MESSAGES
    // ═══════════════════════════════════════════
    private function templateDepotConfirme(array $depot): string
    {
        $retrait = $depot['date_livraison_prevue']
            ? date('d/m/Y', strtotime($depot['date_livraison_prevue']))
            : 'à confirmer';

        return "
        Bonjour {$depot['nomclient']},<br><br>
        Nous avons bien reçu votre dépôt.<br><br>
        <strong>N° de bon :</strong> {$depot['code_commande']}<br>
        <strong>Nombre d'articles :</strong> {$depot['nb_articles']}<br>
        <strong>Montant total :</strong> " . number_format($depot['total_ttc'], 0, ',', ' ') . " FCFA<br>
        <strong>Date de retrait prévue :</strong> {$retrait}<br><br>
        Conservez ce numéro de bon pour le retrait.<br><br>
        Merci de votre confiance,<br>
        <strong>L'équipe Pressing Pro</strong>
        ";
    }

    private function templateCommandePrete(array $depot): string
    {
        return "
        Bonjour {$depot['nomclient']},<br><br>
        🎉 Bonne nouvelle ! Votre commande <strong>{$depot['code_commande']}</strong>
        est <strong>prête à être retirée</strong>.<br><br>
        Vous pouvez passer la récupérer dès maintenant pendant nos heures d'ouverture.<br><br>
        N'oubliez pas votre bon de dépôt.<br><br>
        À bientôt,<br>
        <strong>L'équipe Pressing Pro</strong>
        ";
    }

    private function templateRappel(array $depot): string
    {
        return "
        Bonjour {$depot['nomclient']},<br><br>
        ⏰ Votre commande <strong>{$depot['code_commande']}</strong> est prête depuis plusieurs jours
        et attend toujours d'être retirée.<br><br>
        Merci de passer la récupérer dans les meilleurs délais.<br><br>
        Passé 30 jours, nous ne pouvons plus garantir la conservation de vos articles.<br><br>
        <strong>L'équipe Pressing Pro</strong>
        ";
    }

    private function templateRetraitConfirme(array $depot): string
    {
        return "
        Bonjour {$depot['nomclient']},<br><br>
        ✅ Votre retrait a bien été enregistré.<br><br>
        <strong>Bon n° :</strong> {$depot['code_commande']}<br>
        <strong>Montant réglé :</strong> " . number_format($depot['total_ttc'], 0, ',', ' ') . " FCFA<br><br>
        Merci de votre confiance. À très bientôt !<br><br>
        <strong>L'équipe Pressing Pro</strong>
        ";
    }

    // Enveloppe HTML pour les emails
    private function wrapEmailHtml(string $sujet, string $corps): string
    {
        return "
        <!DOCTYPE html><html><head><meta charset='UTF-8'></head>
        <body style='font-family:Arial,sans-serif;background:#f5f5f5;padding:20px;'>
            <div style='max-width:560px;margin:0 auto;background:#fff;
                        border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);'>
                <div style='background:#1a1a2e;padding:20px 24px;'>
                    <h2 style='color:#fff;margin:0;font-size:16px;'>Pressing Pro</h2>
                </div>
                <div style='padding:24px;font-size:14px;color:#333;line-height:1.7;'>
                    {$corps}
                </div>
                <div style='background:#f8fafc;padding:14px 24px;font-size:11px;color:#999;border-top:1px solid #eee;'>
                    Vous recevez ce message car vous êtes client de Pressing Pro.
                </div>
            </div>
        </body></html>";
    }

    // ═══════════════════════════════════════════
    // UTILITAIRES PRIVÉS
    // ═══════════════════════════════════════════
    private function creerNotification(
        int $clientId, ?int $depotId, string $type,
        string $canal, string $sujet, string $message
    ): void {
        $this->db->table('notifications')->insert([
            'client_id'  => $clientId,
            'depot_id'   => $depotId,
            'type'       => $type,
            'canal'      => $canal,
            'sujet'      => $sujet,
            'message'    => $message,
            'statut'     => 'en_attente',
            'lu'         => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function marquerEnvoye(int $clientId, ?int $depotId, string $type, string $canal): void
    {
        $this->db->table('notifications')
            ->where('client_id', $clientId)
            ->where('depot_id',  $depotId)
            ->where('type',      $type)
            ->where('canal',     $canal)
            ->where('statut',    'en_attente')
            ->update(['statut' => 'envoye', 'date_envoi' => date('Y-m-d H:i:s')]);
    }

    private function marquerEchec(
        int $clientId, ?int $depotId, string $type, string $canal, string $erreur
    ): void {
        $this->db->table('notifications')
            ->where('client_id', $clientId)
            ->where('depot_id',  $depotId)
            ->where('type',      $type)
            ->where('canal',     $canal)
            ->where('statut',    'en_attente')
            ->update(['statut' => 'echec', 'erreur_detail' => $erreur]);
    }

    private function getDepotAvecClient(int $depotId): ?array
    {
        $depot = $this->db->table('depots d')
            ->select('d.*, c.nomclient, c.telephone, c.email,
                      COUNT(da.id_article_depose) AS nb_articles')
            ->join('clients c',         'c.id_client = d.client_id')
            ->join('depot_articles da', 'da.depot_id = d.id_depot', 'left')
            ->where('d.id_depot', $depotId)
            ->groupBy('d.id_depot')
            ->get()->getRowArray();

        return $depot ?: null;
    }

    private function getCiblesCampagne(array $campagne): array
    {
        $builder = $this->db->table('clients');

        switch ($campagne['type_cible']) {
            case 'inactifs':
                $jours = $campagne['jours_inactivite'] ?? 60;
                return $this->db->query("
                    SELECT c.* FROM clients c
                    WHERE c.id_client NOT IN (
                        SELECT client_id FROM depots
                        WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
                    )
                ", [$jours])->getResultArray();

            case 'anniversaire':
                $aujourdHui = date('d') . ' ' . date('F'); // format "15 May"
                return $builder->like('journaissance', date('d'), 'after')->get()->getResultArray();

            case 'tous':
            default:
                return $builder->get()->getResultArray();
        }
    }
}