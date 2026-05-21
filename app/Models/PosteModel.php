<?php
namespace App\Models;
use CodeIgniter\Model;

class PosteModel extends Model
{
    protected $table         = 'postes';
    protected $primaryKey    = 'id_poste';
    protected $allowedFields = ['nom_poste', 'salaire', 'created_at'];
    protected $useTimestamps = false;
}