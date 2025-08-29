import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Calendar, User, Shield, Crown, Mail, CheckCircle, XCircle } from 'lucide-react';

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
        permissions: Array<{
            id: number;
            name: string;
            guard_name: string;
        }>;
    }>;
    permissions: Array<{
        id: number;
        name: string;
        guard_name: string;
    }>;
}

interface UsersShowProps {
    user: User;
}

export default function UsersShow({ user }: UsersShowProps) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    // Get all permissions (from roles + direct permissions)
    const allPermissions = new Set([
        ...user.roles.flatMap(role => role.permissions.map(p => p.name)),
        ...user.permissions.map(p => p.name)
    ]);

    return (
        <AppLayout>
            <Head title={`User: ${user.name}`} />

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
                            <h1 className="text-2xl font-semibold tracking-tight">{user.name}</h1>
                            <p className="text-muted-foreground">
                                User details and permissions.
                            </p>
                        </div>
                    </div>
                    <Link href={`/users/${user.id}/edit`}>
                        <Button>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit User
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>User Information</CardTitle>
                            <CardDescription>
                                Basic information about this user.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center space-x-2">
                                <User className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Name</h3>
                                    <p className="text-lg font-semibold">{user.name}</p>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Mail className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Email</h3>
                                    <p className="text-sm">{user.email}</p>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                {user.email_verified_at ? (
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                ) : (
                                    <XCircle className="h-4 w-4 text-red-500" />
                                )}
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Email Status</h3>
                                    <p className="text-sm">
                                        {user.email_verified_at ? 'Verified' : 'Unverified'}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Account Metadata</CardTitle>
                            <CardDescription>
                                Creation and modification details.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center space-x-2">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Created</h3>
                                    <p className="text-sm">{formatDate(user.created_at)}</p>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Last Updated</h3>
                                    <p className="text-sm">{formatDate(user.updated_at)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <Shield className="mr-2 h-5 w-5" />
                            Assigned Roles
                        </CardTitle>
                        <CardDescription>
                            Roles assigned to this user and their permissions.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {user.roles.length > 0 ? (
                            <div className="space-y-4">
                                {user.roles.map((role) => (
                                    <div key={role.id} className="border rounded-lg p-4">
                                        <div className="flex items-center justify-between mb-2">
                                            <h4 className="font-medium">{role.name}</h4>
                                            <Badge variant="secondary">{role.permissions.length} permissions</Badge>
                                        </div>
                                        {role.permissions.length > 0 && (
                                            <div className="flex flex-wrap gap-1">
                                                {role.permissions.map((permission) => (
                                                    <Badge key={permission.id} variant="outline">
                                                        {permission.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-muted-foreground">No roles assigned to this user.</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <Crown className="mr-2 h-5 w-5" />
                            Direct Permissions
                        </CardTitle>
                        <CardDescription>
                            Permissions directly assigned to this user (in addition to role permissions).
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {user.permissions.length > 0 ? (
                            <div className="flex flex-wrap gap-2">
                                {user.permissions.map((permission) => (
                                    <Badge key={permission.id} variant="default">
                                        {permission.name}
                                    </Badge>
                                ))}
                            </div>
                        ) : (
                            <p className="text-muted-foreground">No direct permissions assigned to this user.</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>All Permissions Summary</CardTitle>
                        <CardDescription>
                            Complete list of all permissions this user has access to (from roles + direct assignments).
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {allPermissions.size > 0 ? (
                            <div className="flex flex-wrap gap-2">
                                {Array.from(allPermissions).map((permission) => (
                                    <Badge key={permission} variant="outline">
                                        {permission}
                                    </Badge>
                                ))}
                            </div>
                        ) : (
                            <p className="text-muted-foreground">This user has no permissions.</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Actions</CardTitle>
                        <CardDescription>
                            Available actions for this user.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2">
                            <Link href={`/users/${user.id}/edit`}>
                                <Button variant="outline">
                                    <Edit className="mr-2 h-4 w-4" />
                                    Edit User
                                </Button>
                            </Link>
                            <Link href="/users">
                                <Button variant="ghost">
                                    View All Users
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
