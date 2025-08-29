import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import UserRoleManager from '../../../components/UserRoleManager';
import { ArrowLeft, User, Mail, Calendar, Shield } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: Array<{
        id: number;
        name: string;
        guard_name: string;
        created_at: string;
        updated_at: string;
    }>;
    permissions: Array<{
        id: number;
        name: string;
        guard_name: string;
        created_at: string;
        updated_at: string;
    }>;
}

interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions: Array<{
        id: number;
        name: string;
        guard_name: string;
    }>;
}

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Props {
    user: User;
    allRoles: Role[];
    allPermissions: Permission[];
    permissionsViaRoles: Permission[];
    directPermissions: Permission[];
}

const UserRoleShow: React.FC<Props> = ({
    user,
    allRoles,
    allPermissions,
    permissionsViaRoles,
    directPermissions
}) => {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    return (
        <AppLayout>
            <Head title={`Manage ${user.name}'s Roles`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link
                            href={route('users.index')}
                            className="flex items-center text-gray-600 hover:text-gray-900"
                        >
                            <ArrowLeft className="h-5 w-5 mr-2" />
                            Back to Users
                        </Link>
                    </div>
                </div>

                {/* User Information Card */}
                <div className="bg-white rounded-lg border border-gray-200 p-6">
                    <div className="flex items-start justify-between">
                        <div className="flex items-center space-x-4">
                            <div className="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                <User className="h-8 w-8 text-gray-400" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-semibold text-gray-900">{user.name}</h1>
                                <div className="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                                    <div className="flex items-center">
                                        <Mail className="h-4 w-4 mr-1" />
                                        {user.email}
                                    </div>
                                    <div className="flex items-center">
                                        <Calendar className="h-4 w-4 mr-1" />
                                        Joined {formatDate(user.created_at)}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center space-x-2">
                            {user.email_verified_at ? (
                                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Verified
                                </span>
                            ) : (
                                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Unverified
                                </span>
                            )}
                        </div>
                    </div>
                </div>

                {/* Role and Permission Management */}
                <UserRoleManager
                    user={user}
                    availableRoles={allRoles}
                    availablePermissions={allPermissions}
                    permissionsViaRoles={permissionsViaRoles}
                    canManageUsers={true}
                    isCurrentUser={false}
                />

                {/* Role Details */}
                {user.roles.length > 0 && (
                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Shield className="h-5 w-5 text-gray-400 mr-2" />
                            Role Details
                        </h3>

                        <div className="space-y-4">
                            {user.roles.map((role) => {
                                const fullRole = allRoles.find(r => r.id === role.id);
                                return (
                                    <div key={role.id} className="border border-gray-200 rounded-lg p-4">
                                        <div className="flex items-center justify-between mb-3">
                                            <h4 className="text-md font-medium text-gray-900 capitalize">
                                                {role.name} Role
                                            </h4>
                                            <span className="text-xs text-gray-500">
                                                {fullRole?.permissions?.length || 0} permissions
                                            </span>
                                        </div>

                                        {fullRole?.permissions && fullRole.permissions.length > 0 && (
                                            <div>
                                                <p className="text-sm text-gray-600 mb-2">Permissions included:</p>
                                                <div className="flex flex-wrap gap-1">
                                                    {fullRole.permissions.map((permission) => (
                                                        <span
                                                            key={permission.id}
                                                            className="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200"
                                                        >
                                                            {permission.name}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* Permission Summary */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">
                            Permissions Summary
                        </h3>

                        <div className="space-y-4">
                            <div>
                                <h4 className="text-sm font-medium text-gray-700 mb-2">
                                    Total Permissions: {permissionsViaRoles.length + directPermissions.length}
                                </h4>
                                <div className="text-sm text-gray-600 space-y-1">
                                    <p>• {permissionsViaRoles.length} via roles</p>
                                    <p>• {directPermissions.length} direct assignments</p>
                                </div>
                            </div>

                            <div>
                                <h4 className="text-sm font-medium text-gray-700 mb-2">
                                    Roles: {user.roles.length}
                                </h4>
                                <div className="flex flex-wrap gap-1">
                                    {user.roles.map((role) => (
                                        <span
                                            key={role.id}
                                            className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded border border-blue-200"
                                        >
                                            {role.name}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">
                            Account Information
                        </h3>

                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-600">User ID:</span>
                                <span className="text-gray-900 font-medium">#{user.id}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">Email Status:</span>
                                <span className={`font-medium ${user.email_verified_at ? 'text-green-600' : 'text-yellow-600'
                                    }`}>
                                    {user.email_verified_at ? 'Verified' : 'Unverified'}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">Member Since:</span>
                                <span className="text-gray-900 font-medium">
                                    {formatDate(user.created_at)}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">Last Updated:</span>
                                <span className="text-gray-900 font-medium">
                                    {formatDate(user.updated_at)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default UserRoleShow;
