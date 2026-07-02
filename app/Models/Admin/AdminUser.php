<?php

namespace App\Models\Admin; 

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable {
  use HasFactory, Notifiable;

  protected $table = 'odms_admin_users';

protected $fillable = [
    'department',
    'email',
    'password',
    'role',
    'permission_level',
    'is_active',
];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }
}