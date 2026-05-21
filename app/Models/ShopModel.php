<?php
namespace App\Models;
use CodeIgniter\Model;

class ShopModel extends Model
{
    protected $table         = 'shops';
    protected $primaryKey    = 'id_shop';
    protected $allowedFields = ['nom_shop', 'adresse', 'created_at'];
    protected $useTimestamps = false;
}