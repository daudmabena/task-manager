import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import {
    Users,
    Search,
    Crown,
    Shield,
    Plus,
    MoreHorizontal,
    Eye,
    Mail,
    Calendar
} from 'lucide-react';

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
    }>;
    permissions: Array<{
        id: number;
        name: string;
        guard_name: string;
    }>;
}

interface Role {
    id: number;
    name: string;
    guard_name: string;
}

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    roles: Role[];
    permissions: Permission[];
    filters: {
        search?: string;
    };
}

const UserRoleIndex: React.FC<Props> = ({ users, roles, permissions, filters }) => {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedUsers, setSelectedUsers] = useState<number[]>([]);
    const [bulkRole, setBulkRole] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('users.index'), { search }, { preserveState: true });
    };

    const handleBulkAssignRole = () => {
        if (selectedUsers.length === 0 || !bulkRole) return;

        router.post(route('users.bulk-assign-role'), {
            user_ids: selectedUsers,
            role: bulkRole
        }, {
            onSuccess: () => {
                setSelectedUsers([]);
                setBulkRole('');
            }
        });
    };

    const toggleUserSelection = (userId: number) => {
        setSelectedUsers(prev =>
            prev.includes(userId)
                ? prev.filter(id => id !== userId)
                : [...prev, userId]
        );
    };

    const getRoleBadgeColor = (roleName: string) => {
        switch (roleName) {
            case 'admin': return 'bg-red-100 text-red-800 border-red-200';
            case 'manager': return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'user': return 'bg-green-100 text-green-800 border-green-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    return (
        <AppLayout>
            <Head title="User Role Management" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 flex items-center">
                            <Users className="h-6 w-6 mr-3 text-gray-400" />
                            User Role Management
                        </h1>
                        <p className="text-sm text-gray-600 mt-1">
                            Manage user roles and permissions across your application
                        </p>
                    </div>

                    <div className="flex items-center space-x-3">
                        <span className="text-sm text-gray-600">
                            {users.total} users total
                        </span>
                    </div>
                </div>

                {/* Search and Filters */}
                <div className="bg-white rounded-lg border border-gray-200 p-4">
                    <div className="flex items-center space-x-4">
                        <form onSubmit={handleSearch} className="flex-1">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search users by name or email..."
                                    className="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </form>

                        <button
                            onClick={handleSearch}
                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center"
                        >
                            <Search className="h-4 w-4 mr-2" />
                            Search
                        </button>
                    </div>

                    {/* Bulk Actions */}
                    {selectedUsers.length > 0 && (
                        <div className="mt-4 pt-4 border-t border-gray-200">
                            <div className="flex items-center space-x-4">
                                <span className="text-sm text-gray-600">
                                    {selectedUsers.length} user{selectedUsers.length > 1 ? 's' : ''} selected
                                </span>

                                <div className="flex items-center space-x-2">
                                    <select
                                        value={bulkRole}
                                        onChange={(e) => setBulkRole(e.target.value)}
                                        className="border border-gray-300 rounded-md px-3 py-1 text-sm"
                                    >
                                        <option value="">Select role to assign...</option>
                                        {roles.map((role) => (
                                            <option key={role.id} value={role.name}>
                                                {role.name}
                                            </option>
                                        ))}
                                    </select>

                                    <button
                                        onClick={handleBulkAssignRole}
                                        disabled={!bulkRole}
                                        className="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                                    >
                                        <Plus className="h-3 w-3 mr-1" />
                                        Assign Role
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Users Table */}
                <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            onChange={(e) => {
                                                if (e.target.checked) {
                                                    setSelectedUsers(users.data.map(u => u.id));
                                                } else {
                                                    setSelectedUsers([]);
                                                }
                                            }}
                                            checked={selectedUsers.length === users.data.length && users.data.length > 0}
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Roles
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Permissions
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Joined
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {users.data.map((user) => (
                                    <tr key={user.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4">
                                            <input
                                                type="checkbox"
                                                checked={selectedUsers.includes(user.id)}
                                                onChange={() => toggleUserSelection(user.id)}
                                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                        </td>

                                        <td className="px-6 py-4">
                                            <div className="flex items-center">
                                                <div className="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                                    <Users className="h-4 w-4 text-gray-400" />
                                                </div>
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {user.name}
                                                    </div>
                                                    <div className="text-sm text-gray-500 flex items-center">
                                                        <Mail className="h-3 w-3 mr-1" />
                                                        {user.email}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td className="px-6 py-4">
                                            {user.roles.length > 0 ? (
                                                <div className="flex flex-wrap gap-1">
                                                    {user.roles.map((role) => (
                                                        <span
                                                            key={role.id}
                                                            className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border ${getRoleBadgeColor(role.name)}`}
                                                        >
                                                            <Crown className="h-2 w-2 mr-1" />
                                                            {role.name}
                                                        </span>
                                                    ))}
                                                </div>
                                            ) : (
                                                <span className="text-gray-400 italic text-sm">No roles</span>
                                            )}
                                        </td>

                                        <td className="px-6 py-4">
                                            <div className="flex items-center text-sm text-gray-600">
                                                <Shield className="h-4 w-4 mr-1" />
                                                {user.permissions.length} direct
                                            </div>
                                        </td>

                                        <td className="px-6 py-4">
                                            {user.email_verified_at ? (
                                                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Verified
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Unverified
                                                </span>
                                            )}
                                        </td>

                                        <td className="px-6 py-4 text-sm text-gray-500">
                                            <div className="flex items-center">
                                                <Calendar className="h-3 w-3 mr-1" />
                                                {formatDate(user.created_at)}
                                            </div>
                                        </td>

                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end space-x-2">
                                                <Link
                                                    href={route('users.show', user.id)}
                                                    className="text-blue-600 hover:text-blue-900 p-1"
                                                    title="View user details"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </Link>

                                                <button className="text-gray-400 hover:text-gray-600 p-1">
                                                    <MoreHorizontal className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {users.last_page > 1 && (
                        <div className="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-700">
                                    Showing {((users.current_page - 1) * users.per_page) + 1} to {Math.min(users.current_page * users.per_page, users.total)} of {users.total} results
                                </div>

                                <div className="flex items-center space-x-2">
                                    {users.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-3 py-2 text-sm rounded-md ${link.active
                                                ? 'bg-blue-600 text-white'
                                                : link.url
                                                    ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                }`}
                                            preserveState
                                        >
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Crown className="h-5 w-5 text-yellow-500 mr-2" />
                            Available Roles
                        </h3>

                        <div className="space-y-2">
                            {roles.map((role) => (
                                <div key={role.id} className="flex items-center justify-between">
                                    <span className={`px-2 py-1 rounded text-xs font-medium ${getRoleBadgeColor(role.name)}`}>
                                        {role.name}
                                    </span>
                                    <span className="text-sm text-gray-500">
                                        {users.data.filter(u => u.roles.some(r => r.name === role.name)).length} users
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Shield className="h-5 w-5 text-blue-500 mr-2" />
                            Permission Overview
                        </h3>

                        <div className="text-sm text-gray-600 space-y-1">
                            <p>Total permissions: {permissions.length}</p>
                            <p>Users with direct permissions: {users.data.filter(u => u.permissions.length > 0).length}</p>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Users className="h-5 w-5 text-green-500 mr-2" />
                            User Statistics
                        </h3>

                        <div className="text-sm text-gray-600 space-y-1">
                            <p>Total users: {users.total}</p>
                            <p>Verified: {users.data.filter(u => u.email_verified_at).length}</p>
                            <p>Unverified: {users.data.filter(u => !u.email_verified_at).length}</p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default UserRoleIndex;
