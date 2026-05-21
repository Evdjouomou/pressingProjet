<?php

namespace App\Models;

use CodeIgniter\Model;

class ReceptionnisteModel extends Model {
    protected $table = 'clients';
    protected $primaryKey = 'id_client';
    protected $allowedFields = [
        'nomclient', 'email', 'telephone', 'adresse', 'journaissance', 'dateajout'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'dateajout';
    protected $updatedField = '';

    public function getAllClients() {
        return $this->findAll();
    }

    public function getClientById($id_client) {
        return $this->where('id_client', $id_client)->first();
    }
}