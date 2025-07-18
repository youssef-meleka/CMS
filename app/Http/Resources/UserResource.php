<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role, // Backward compatibility
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Additional user statistics
            'statistics' => [
                'created_products_count' => $this->whenLoaded('createdProducts', function () {
                    return $this->createdProducts->count();
                }),
                'orders_count' => $this->whenLoaded('orders', function () {
                    return $this->orders->count();
                }),
                'assigned_orders_count' => $this->whenLoaded('assignedOrders', function () {
                    return $this->assignedOrders->count();
                }),
            ],

            // Permission checks for common actions
            'can' => [
                'manage_users' => $this->hasPermissionTo('manage users'),
                'manage_products' => $this->hasPermissionTo('manage products'),
                'manage_orders' => $this->hasPermissionTo('manage orders'),
                'access_dashboard' => $this->hasPermissionTo('access dashboard'),
                'view_statistics' => $this->hasPermissionTo('view statistics'),
            ],
        ];
    }
}
