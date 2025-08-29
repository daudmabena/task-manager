import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Edit, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Role {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
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

interface RolesProps {
    roles: {
        data: Role[];
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
    permissions: Permission[];
}

export default function Roles({ roles, permissions }: RolesProps) {
    const [createDialogOpen, setCreateDialogOpen] = useState(false);
    const [editDialogOpen, setEditDialogOpen] = useState(false);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [selectedRole, setSelectedRole] = useState<Role | null>(null);

    const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({
        name: '',
        guard_name: 'web',
        permissions: [] as string[],
    });

    const handleCreate = () => {
        setSelectedRole(null);
        reset();
        setCreateDialogOpen(true);
    };

    const handleEdit = (role: Role) => {
        setSelectedRole(role);
        setData({
            name: role.name,
            guard_name: role.guard_name,
            permissions: role.permissions.map(p => p.name),
        });
        setEditDialogOpen(true);
    };

    const handleDelete = (role: Role) => {
        setSelectedRole(role);
        setDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        if (selectedRole) {
            destroy(`/roles/${selectedRole.id}`, {
                onSuccess: () => {
                    setDeleteDialogOpen(false);
                    setSelectedRole(null);
                },
            });
        }
    };

    const handleCreateSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/roles', {
            onSuccess: () => {
                setCreateDialogOpen(false);
                reset();
            },
        });
    };

    const handleEditSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (selectedRole) {
            put(`/roles/${selectedRole.id}`, {
                onSuccess: () => {
                    setEditDialogOpen(false);
                    setSelectedRole(null);
                    reset();
                },
            });
        }
    };

    const handlePermissionChange = (permissionName: string, checked: boolean) => {
        if (checked) {
            setData('permissions', [...data.permissions, permissionName]);
        } else {
            setData('permissions', data.permissions.filter(permission => permission !== permissionName));
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
            <Head title="Role Management" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/users">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Users
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">Role Management</h1>
                            <p className="text-muted-foreground">
                                Manage roles and their permissions.
                            </p>
                        </div>
                    </div>
                    <Button onClick={handleCreate}>
                        <Plus className="mr-2 h-4 w-4" />
                        Create Role
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Roles List</CardTitle>
                        <CardDescription>
                            A list of all roles in your system with their assigned permissions.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <div className="relative w-full overflow-auto">
                                <table className="w-full caption-bottom text-sm">
                                    <thead className="[&_tr]:border-b">
                                        <tr className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Role Name
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Guard
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Permissions
                                            </th>
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Created
                                            </th>
                                            <th className="h-12 px-4 text-right align-middle font-medium text-muted-foreground">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="[&_tr:last-child]:border-0">
                                        {roles.data.map((role) => (
                                            <tr key={role.id} className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                                <td className="p-4 align-middle font-medium">
                                                    {role.name}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <Badge variant="outline">{role.guard_name}</Badge>
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <div className="flex flex-wrap gap-1">
                                                        {role.permissions.map((permission) => (
                                                            <Badge key={permission.id} variant="secondary">
                                                                {permission.name}
                                                            </Badge>
                                                        ))}
                                                        {role.permissions.length === 0 && (
                                                            <span className="text-muted-foreground text-sm">No permissions</span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="p-4 align-middle text-muted-foreground">
                                                    {formatDate(role.created_at)}
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleEdit(role)}
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleDelete(role)}
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
                        {roles.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((roles.current_page - 1) * roles.per_page) + 1} to{' '}
                                    {Math.min(roles.current_page * roles.per_page, roles.total)} of{' '}
                                    {roles.total} results
                                </div>
                                <div className="flex items-center space-x-2">
                                    {roles.links.map((link, index) => (
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

            {/* Create Role Dialog */}
            <Dialog open={createDialogOpen} onOpenChange={setCreateDialogOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Create New Role</DialogTitle>
                        <DialogDescription>
                            Create a new role and assign permissions to it.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleCreateSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Role Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Enter role name"
                                className={errors.name ? 'border-red-500' : ''}
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="guard_name">Guard Name</Label>
                            <Input
                                id="guard_name"
                                value={data.guard_name}
                                onChange={(e) => setData('guard_name', e.target.value)}
                                placeholder="web"
                                className={errors.guard_name ? 'border-red-500' : ''}
                            />
                            {errors.guard_name && (
                                <p className="text-sm text-red-500">{errors.guard_name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label>Permissions</Label>
                            <div className="max-h-40 overflow-y-auto space-y-2">
                                {permissions.map((permission) => (
                                    <div key={permission.id} className="flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            id={`create-permission-${permission.id}`}
                                            checked={data.permissions.includes(permission.name)}
                                            onChange={(e) => handlePermissionChange(permission.name, e.target.checked)}
                                            className="rounded border-gray-300"
                                            aria-label={`Select permission ${permission.name}`}
                                        />
                                        <Label htmlFor={`create-permission-${permission.id}`} className="text-sm">
                                            {permission.name}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setCreateDialogOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Role'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Edit Role Dialog */}
            <Dialog open={editDialogOpen} onOpenChange={setEditDialogOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Edit Role</DialogTitle>
                        <DialogDescription>
                            Update role information and permissions.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEditSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="edit-name">Role Name</Label>
                            <Input
                                id="edit-name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Enter role name"
                                className={errors.name ? 'border-red-500' : ''}
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="edit-guard_name">Guard Name</Label>
                            <Input
                                id="edit-guard_name"
                                value={data.guard_name}
                                onChange={(e) => setData('guard_name', e.target.value)}
                                placeholder="web"
                                className={errors.guard_name ? 'border-red-500' : ''}
                            />
                            {errors.guard_name && (
                                <p className="text-sm text-red-500">{errors.guard_name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label>Permissions</Label>
                            <div className="max-h-40 overflow-y-auto space-y-2">
                                {permissions.map((permission) => (
                                    <div key={permission.id} className="flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            id={`edit-permission-${permission.id}`}
                                            checked={data.permissions.includes(permission.name)}
                                            onChange={(e) => handlePermissionChange(permission.name, e.target.checked)}
                                            className="rounded border-gray-300"
                                            aria-label={`Select permission ${permission.name}`}
                                        />
                                        <Label htmlFor={`edit-permission-${permission.id}`} className="text-sm">
                                            {permission.name}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setEditDialogOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Updating...' : 'Update Role'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Role</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{selectedRole?.name}"? This action cannot be undone.
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
