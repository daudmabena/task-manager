import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index as systemsIndex, edit as systemsEdit, update as systemsUpdate } from '@/routes/systems';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface System {
    id: number;
    name: string;
    description: string;
}

interface SystemsEditProps {
    system: System;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Systems',
        href: systemsIndex().url,
    },
    {
        title: 'Edit System',
        href: '#',
    },
];

export default function SystemsEdit({ system }: SystemsEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: system.name,
        description: system.description,
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(systemsUpdate(system.id).url);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit System" />

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
                            <h1 className="text-2xl font-semibold tracking-tight">Edit System</h1>
                            <p className="text-muted-foreground">
                                Update the system information.
                            </p>
                        </div>
                    </div>
                </div>

                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle>System Information</CardTitle>
                        <CardDescription>
                            Update the details for this system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setData('name', e.target.value)}
                                    placeholder="Enter system name"
                                    className={errors.name ? 'border-red-500' : ''}
                                />
                                {errors.name && (
                                    <p className="text-sm text-red-500">{errors.name}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('description', e.target.value)}
                                    placeholder="Enter system description"
                                    rows={4}
                                    className={`flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ${errors.description ? 'border-red-500' : ''
                                        }`}
                                />
                                {errors.description && (
                                    <p className="text-sm text-red-500">{errors.description}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-end space-x-2">
                                <Link href={systemsIndex().url}>
                                    <Button type="button" variant="outline">
                                        Cancel
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    <Save className="mr-2 h-4 w-4" />
                                    {processing ? 'Updating...' : 'Update System'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
