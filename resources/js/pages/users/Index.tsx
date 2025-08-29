import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Plus, Search, Edit, Eye, Trash2, ArrowUpDown, Users, Shield, Crown } from 'lucide-react';
import { useState } from 'react';

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

interface UsersIndexProps {
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
        role?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function UsersIndex({ users, roles, permissions, filters }: UsersIndexProps) {
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [userToDelete, setUserToDelete] = useState<User | null>(null);

    const { delete: destroy } = useForm();

    const handleSearch = (search: string) => {
        router.get('/users', { search, ...filters }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleRoleFilter = (role: string) => {
        router.get('/users', { ...filters, role }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSort = (column: string) => {
        const direction = filters.sort_by === column && filters.sort_direction === 'asc' ? 'desc' : 'asc';
        router.get('/users', { ...filters, sort_by: column, sort_direction: direction }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleDelete = (user: User) => {
        setUserToDelete(user);
        setDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        if (userToDelete) {
            destroy(`/users/${userToDelete.id}`, {
                onSuccess: () => {
                    setDeleteDialogOpen(false);
                    setUserToDelete(null);
                },
            });
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    return (
        <AppLayout>
            <Head title="User Management" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">User Management</h1>
                        <p className="text-muted-foreground">
                            Manage user accounts, roles, and permissions.
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href="/users/roles">
                            <Button variant="outline">
                                <Shield className="mr-2 h-4 w-4" />
                                Manage Roles
                            </Button>
                        </Link>
                        <Link href="/users/permissions">
                            <Button variant="outline">
                                <Crown className="mr-2 h-4 w-4" />
                                Manage Permissions
                            </Button>
                        </Link>
                        <Link href="/users/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Add User
                            </Button>
                        </Link>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Users List</CardTitle>
                        <CardDescription>
                            A list of all users in your organization with their roles and permissions.
                        </CardDescription>
                        <div className="flex items-center space-x-2">
                            <div className="relative flex-1 max-w-sm">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search users..."
                                    defaultValue={filters.search}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    className="pl-8"
                                />
                            </div>
                            <select
                                value={filters.role || ''}
                                onChange={(e) => handleRoleFilter(e.target.value)}
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                aria-label="Filter by role"
                            >
                                <option value="">All Roles</option>
                                {roles.map((role) => (
                                    <option key={role.id} value={role.name}>
                                        {role.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <div className="relative w-full overflow-auto">
                                <table className="w-full caption-bottom text-sm">
                                    <thead className="[&_tr]:border-b">
                                        <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort('name')}
                                                    className="h-auto p-0 font-medium"
                                                >
                                                    Name
                                                    <ArrowUpDown className="ml-2 h-4 w-4" />
                                                </Button>
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort('email')}
                                                    className="h-auto p-0 font-medium"
                                                >
                                                    Email
                                                    <ArrowUpDown className="ml-2 h-4 w-4" />
                                                </Button>
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Roles
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Permissions
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Status
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort('created_at')}
                                                    className="h-auto p-0 font-medium"
                                                >
                                                    Created
                                                    <ArrowUpDown className="ml-2 h-4 w-4" />
                                                </Button>
                                            </th>
                                            <th className="h-12 px-4 text-right align-middle font-medium text-muted-foreground">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="[&_tr:last-child]:border-0">
                                        {users.data.map((user) => (
                                            <tr key={user.id} className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                                <td className="p-4 align-middle font-medium">
                                                    {user.name}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    {user.email}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <div className="flex flex-wrap gap-1">
                                                        {user.roles.map((role) => (
                                                            <Badge key={role.id} variant="secondary">
                                                                {role.name}
                                                            </Badge>
                                                        ))}
                                                        {user.roles.length === 0 && (
                                                            <span className="text-muted-foreground text-sm">No roles</span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <div className="flex flex-wrap gap-1">
                                                        {user.permissions.map((permission) => (
                                                            <Badge key={permission.id} variant="outline">
                                                                {permission.name}
                                                            </Badge>
                                                        ))}
                                                        {user.permissions.length === 0 && (
                                                            <span className="text-muted-foreground text-sm">No direct permissions</span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <Badge variant={user.email_verified_at ? "default" : "secondary"}>
                                                        {user.email_verified_at ? "Verified" : "Unverified"}
                                                    </Badge>
                                                </td>
                                                <td className="p-4 align-middle text-muted-foreground">
                                                    {formatDate(user.created_at)}
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Link href={`/users/${user.id}`}>
                                                            <Button variant="ghost" size="sm">
                                                                <Eye className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Link href={`/users/${user.id}/edit`}>
                                                            <Button variant="ghost" size="sm">
                                                                <Edit className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleDelete(user)}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {/* Pagination */}
                        {users.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((users.current_page - 1) * users.per_page) + 1} to{' '}
                                    {Math.min(users.current_page * users.per_page, users.total)} of{' '}
                                    {users.total} results
                                </div>
                                <div className="flex items-center space-x-2">
                                    {users.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-3 py-2 text-sm rounded-md ${link.active
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                                }`}
                                        >
                                            {link.label}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Delete Confirmation Dialog */}
            <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete User</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{userToDelete?.name}"? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteDialogOpen(false)}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
