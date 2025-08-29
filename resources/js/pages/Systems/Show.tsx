import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { index as systemsIndex, edit as systemsEdit } from '@/routes/systems';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Calendar, User } from 'lucide-react';

interface System {
    id: number;
    name: string;
    slug: string;
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

interface SystemsShowProps {
    system: System;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Systems',
        href: systemsIndex().url,
    },
    {
        title: 'System Details',
        href: '#',
    },
];

export default function SystemsShow({ system }: SystemsShowProps) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`System: ${system.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href={systemsIndex().url}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Systems
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">{system.name}</h1>
                            <p className="text-muted-foreground">
                                System details and information.
                            </p>
                        </div>
                    </div>
                    <Link href={systemsEdit({ slug: system.slug }).url}>
                        <Button>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit System
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>System Information</CardTitle>
                            <CardDescription>
                                Basic information about this system.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <h3 className="font-medium text-sm text-muted-foreground">Name</h3>
                                <p className="text-lg font-semibold">{system.name}</p>
                            </div>
                            <div>
                                <h3 className="font-medium text-sm text-muted-foreground">Description</h3>
                                <p className="text-sm leading-relaxed">{system.description}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>System Metadata</CardTitle>
                            <CardDescription>
                                Creation and modification details.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center space-x-2">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Created</h3>
                                    <p className="text-sm">{formatDate(system.created_at)}</p>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Last Updated</h3>
                                    <p className="text-sm">{formatDate(system.updated_at)}</p>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <User className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Created By</h3>
                                    <p className="text-sm">{system.creator?.name || 'Unknown'}</p>
                                </div>
                            </div>
                            {system.updater && system.updater.name !== system.creator?.name && (
                                <div className="flex items-center space-x-2">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <h3 className="font-medium text-sm text-muted-foreground">Last Updated By</h3>
                                        <p className="text-sm">{system.updater.name}</p>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Actions</CardTitle>
                        <CardDescription>
                            Available actions for this system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2">
                            <Link href={systemsEdit({ slug: system.slug }).url}>
                                <Button variant="outline">
                                    <Edit className="mr-2 h-4 w-4" />
                                    Edit System
                                </Button>
                            </Link>
                            <Link href={systemsIndex().url}>
                                <Button variant="ghost">
                                    View All Systems
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
