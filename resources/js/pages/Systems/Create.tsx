import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import AppLayout from '@/layouts/app-layout';
import { index as systemsIndex, create as systemsCreate, store as systemsStore } from '@/routes/systems';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Systems',
        href: systemsIndex().url,
    },
    {
        title: 'Create System',
        href: systemsCreate().url,
    },
];

export default function SystemsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(systemsStore().url);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create System" />

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
                            <h1 className="text-2xl font-semibold tracking-tight">Create System</h1>
                            <p className="text-muted-foreground">
                                Add a new system to your organization.
                            </p>
                        </div>
                    </div>
                </div>

                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle>System Information</CardTitle>
                        <CardDescription>
                            Enter the details for the new system.
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
                                    {processing ? 'Creating...' : 'Create System'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
