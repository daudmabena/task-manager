import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { 
    User, 
    Shield, 
    Plus, 
    Trash2, 
    CheckCircle, 
    AlertCircle,
    Crown,
    Lock
} from 'lucide-react';
import { showToast } from '../utils/toast';

interface Role {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
    permissions?: Permission[];
}

interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

interface UserRoleManagerProps {
    user: {
        id: number;
        name: string;
        email: string;
        roles: Role[];
        permissions: Permission[];
    };
    availableRoles: Role[];
    availablePermissions: Permission[];
    permissionsViaRoles: Permission[];
    canManageUsers: boolean;
    isCurrentUser?: boolean;
}

const UserRoleManager: React.FC<UserRoleManagerProps> = ({
    user,
    availableRoles,
    availablePermissions,
    permissionsViaRoles,
    canManageUsers,
    isCurrentUser = false
}) => {
    const [selectedRole, setSelectedRole] = useState('');
    const [selectedPermission, setSelectedPermission] = useState('');
    
    const assignRoleForm = useForm({
        role: ''
    });
    
    const removeRoleForm = useForm({
        role: ''
    });
    
    const assignPermissionForm = useForm({
        permission: ''
    });
    
    const removePermissionForm = useForm({
        permission: ''
    });

    const handleAssignRole = (roleId: string) => {
        const role = availableRoles.find(r => r.id.toString() === roleId);
        if (!role) return;
        
        assignRoleForm.setData('role', role.name);
        assignRoleForm.post(route('users.assign-role', user.id), {
            onSuccess: () => {
                showToast.success(`Role "${role.name}" assigned successfully!`);
                setSelectedRole('');
            },
            onError: () => {
                showToast.error('Failed to assign role');
            }
        });
    };
    
    const handleRemoveRole = (roleName: string) => {
        removeRoleForm.setData('role', roleName);
        removeRoleForm.delete(route('users.remove-role', user.id), {
            onSuccess: () => {
                showToast.success(`Role "${roleName}" removed successfully!`);
            },
            onError: () => {
                showToast.error('Failed to remove role');
            }
        });
    };
    
    const handleAssignPermission = (permissionId: string) => {
        const permission = availablePermissions.find(p => p.id.toString() === permissionId);
        if (!permission) return;
        
        assignPermissionForm.setData('permission', permission.name);
        assignPermissionForm.post(route('users.assign-permission', user.id), {
            onSuccess: () => {
                showToast.success(`Permission "${permission.name}" assigned successfully!`);
                setSelectedPermission('');
            },
            onError: () => {
                showToast.error('Failed to assign permission');
            }
        });
    };
    
    const handleRemovePermission = (permissionName: string) => {
        removePermissionForm.setData('permission', permissionName);
        removePermissionForm.delete(route('users.remove-permission', user.id), {
            onSuccess: () => {
                showToast.success(`Permission "${permissionName}" removed successfully!`);
            },
            onError: () => {
                showToast.error('Failed to remove permission');
            }
        });
    };

    const getRoleBadgeColor = (roleName: string) => {
        switch (roleName) {
            case 'admin': return 'bg-red-100 text-red-800 border-red-200';
            case 'manager': return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'user': return 'bg-green-100 text-green-800 border-green-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const getPermissionIcon = (permissionName: string) => {
        if (permissionName.includes('manage')) return <Crown className="h-3 w-3" />;
        if (permissionName.includes('create')) return <Plus className="h-3 w-3" />;
        if (permissionName.includes('delete')) return <Trash2 className="h-3 w-3" />;
        return <Lock className="h-3 w-3" />;
    };

    const availableRolesToAssign = availableRoles.filter(
        role => !user.roles.some(userRole => userRole.id === role.id)
    );

    const availablePermissionsToAssign = availablePermissions.filter(
        permission => !user.permissions.some(userPerm => userPerm.id === permission.id) &&
                     !permissionsViaRoles.some(rolePerm => rolePerm.id === permission.id)
    );

    if (!canManageUsers && !isCurrentUser) {
        return (
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div className="flex items-center">
                    <AlertCircle className="h-5 w-5 text-yellow-400 mr-2" />
                    <p className="text-sm text-yellow-800">
                        You don't have permission to manage user roles.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Current Roles */}
            <div className="bg-white rounded-lg border border-gray-200 p-6">
                <div className="flex items-center mb-4">
                    <Shield className="h-5 w-5 text-gray-400 mr-2" />
                    <h3 className="text-lg font-medium text-gray-900">Current Roles</h3>
                </div>
                
                {user.roles.length > 0 ? (
                    <div className="space-y-2">
                        {user.roles.map((role) => (
                            <div
                                key={role.id}
                                className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border mr-2 mb-2 ${getRoleBadgeColor(role.name)}`}
                            >
                                <Crown className="h-3 w-3 mr-1" />
                                {role.name}
                                {canManageUsers && (
                                    <button
                                        onClick={() => handleRemoveRole(role.name)}
                                        className="ml-2 text-red-400 hover:text-red-600"
                                        disabled={removeRoleForm.processing}
                                        title="Remove role"
                                    >
                                        <Trash2 className="h-3 w-3" />
                                    </button>
                                )}
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="text-gray-500 italic">No roles assigned</p>
                )}

                {/* Assign New Role */}
                {canManageUsers && availableRolesToAssign.length > 0 && (
                    <div className="mt-4 pt-4 border-t border-gray-200">
                        <div className="flex items-center space-x-2">
                            <select
                                value={selectedRole}
                                onChange={(e) => setSelectedRole(e.target.value)}
                                className="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm"
                            >
                                <option value="">Select a role to assign...</option>
                                {availableRolesToAssign.map((role) => (
                                    <option key={role.id} value={role.id}>
                                        {role.name}
                                    </option>
                                ))}
                            </select>
                            <button
                                onClick={() => selectedRole && handleAssignRole(selectedRole)}
                                disabled={!selectedRole || assignRoleForm.processing}
                                className="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <Plus className="h-4 w-4 mr-1" />
                                Assign
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Current Permissions */}
            <div className="bg-white rounded-lg border border-gray-200 p-6">
                <div className="flex items-center mb-4">
                    <Lock className="h-5 w-5 text-gray-400 mr-2" />
                    <h3 className="text-lg font-medium text-gray-900">Current Permissions</h3>
                </div>

                {/* Permissions via Roles */}
                {permissionsViaRoles.length > 0 && (
                    <div className="mb-4">
                        <h4 className="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <CheckCircle className="h-4 w-4 text-green-500 mr-1" />
                            Via Roles
                        </h4>
                        <div className="space-y-1">
                            {permissionsViaRoles.map((permission) => (
                                <span
                                    key={permission.id}
                                    className="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-800 border border-green-200 mr-1 mb-1"
                                >
                                    {getPermissionIcon(permission.name)}
                                    <span className="ml-1">{permission.name}</span>
                                </span>
                            ))}
                        </div>
                    </div>
                )}

                {/* Direct Permissions */}
                {user.permissions.length > 0 && (
                    <div className="mb-4">
                        <h4 className="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <User className="h-4 w-4 text-blue-500 mr-1" />
                            Direct Permissions
                        </h4>
                        <div className="space-y-1">
                            {user.permissions.map((permission) => (
                                <span
                                    key={permission.id}
                                    className="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200 mr-1 mb-1"
                                >
                                    {getPermissionIcon(permission.name)}
                                    <span className="ml-1">{permission.name}</span>
                                    {canManageUsers && (
                                        <button
                                            onClick={() => handleRemovePermission(permission.name)}
                                            className="ml-1 text-red-400 hover:text-red-600"
                                            disabled={removePermissionForm.processing}
                                            title="Remove permission"
                                        >
                                            <Trash2 className="h-2 w-2" />
                                        </button>
                                    )}
                                </span>
                            ))}
                        </div>
                    </div>
                )}

                {permissionsViaRoles.length === 0 && user.permissions.length === 0 && (
                    <p className="text-gray-500 italic">No permissions assigned</p>
                )}

                {/* Assign New Permission */}
                {canManageUsers && availablePermissionsToAssign.length > 0 && (
                    <div className="mt-4 pt-4 border-t border-gray-200">
                        <div className="flex items-center space-x-2">
                            <select
                                value={selectedPermission}
                                onChange={(e) => setSelectedPermission(e.target.value)}
                                className="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm"
                            >
                                <option value="">Select a permission to assign...</option>
                                {availablePermissionsToAssign.map((permission) => (
                                    <option key={permission.id} value={permission.id}>
                                        {permission.name}
                                    </option>
                                ))}
                            </select>
                            <button
                                onClick={() => selectedPermission && handleAssignPermission(selectedPermission)}
                                disabled={!selectedPermission || assignPermissionForm.processing}
                                className="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <Plus className="h-4 w-4 mr-1" />
                                Assign
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default UserRoleManager;
