import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import { index as systemsIndex, create as systemsCreate, show as systemsShow, edit as systemsEdit, destroy as systemsDestroy } from '@/routes/systems';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Plus, Search, Edit, Eye, Trash2, ArrowUpDown } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Systems',
        href: systemsIndex().url,
    },
];

interface System {
    id: number;
    name: string;
    description: string;
    created_at: string;
    updated_at: string;
    creator?: {
        name: string;
    };
    updater?: {
        name: string;
    };
}

interface SystemsIndexProps {
    systems: {
        data: System[];
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
    filters: {
        search?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function SystemsIndex({ systems, filters }: SystemsIndexProps) {
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [systemToDelete, setSystemToDelete] = useState<System | null>(null);

    const { delete: destroy } = useForm();

    const handleSearch = (search: string) => {
        router.get(systemsIndex().url, { search, ...filters }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSort = (column: string) => {
        const direction = filters.sort_by === column && filters.sort_direction === 'asc' ? 'desc' : 'asc';
        router.get(systemsIndex().url, { ...filters, sort_by: column, sort_direction: direction }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleDelete = (system: System) => {
        setSystemToDelete(system);
        setDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        if (systemToDelete) {
            destroy(systemsDestroy(systemToDelete.id).url, {
                onSuccess: () => {
                    setDeleteDialogOpen(false);
                    setSystemToDelete(null);
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
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Systems" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Systems</h1>
                        <p className="text-muted-foreground">
                            Manage your systems and their configurations.
                        </p>
                    </div>
                    <Link href={systemsCreate().url}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add System
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Systems List</CardTitle>
                        <CardDescription>
                            A list of all systems in your organization.
                        </CardDescription>
                        <div className="flex items-center space-x-2">
                            <div className="relative flex-1 max-w-sm">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search systems..."
                                    defaultValue={filters.search}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    className="pl-8"
                                />
                            </div>
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
                                                Description
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
                                            <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                Created By
                                            </th>
                                            <th className="h-12 px-4 text-right align-middle font-medium text-muted-foreground">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="[&_tr:last-child]:border-0">
                                        {systems.data.map((system) => (
                                            <tr key={system.id} className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                                <td className="p-4 align-middle font-medium">
                                                    {system.name}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <div className="max-w-xs truncate">
                                                        {system.description}
                                                    </div>
                                                </td>
                                                <td className="p-4 align-middle text-muted-foreground">
                                                    {formatDate(system.created_at)}
                                                </td>
                                                <td className="p-4 align-middle text-muted-foreground">
                                                    {system.creator?.name || 'Unknown'}
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Link href={systemsShow(system.id).url}>
                                                            <Button variant="ghost" size="sm">
                                                                <Eye className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Link href={systemsEdit(system.id).url}>
                                                            <Button variant="ghost" size="sm">
                                                                <Edit className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleDelete(system)}
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
                        {systems.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((systems.current_page - 1) * systems.per_page) + 1} to{' '}
                                    {Math.min(systems.current_page * systems.per_page, systems.total)} of{' '}
                                    {systems.total} results
                                </div>
                                <div className="flex items-center space-x-2">
                                    {systems.links.map((link, index) => (
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
                        <DialogTitle>Delete System</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{systemToDelete?.name}"? This action cannot be undone.
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
