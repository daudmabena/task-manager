import React from 'react';
import { 
    Calendar, 
    User, 
    Clock, 
    AlertCircle, 
    CheckCircle2, 
    Circle,
    Edit3,
    Trash2,
    MoreHorizontal 
} from 'lucide-react';
import { showToast } from '../utils/toast';

interface TaskCardProps {
    task: {
        id: number;
        title: string;
        description?: string;
        status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
        priority: 'low' | 'medium' | 'high' | 'urgent';
        due_date?: string;
        user?: { name: string };
        assigned_user?: { name: string };
        created_at: string;
        updated_at: string;
    };
    onEdit?: (taskId: number) => void;
    onDelete?: (taskId: number) => void;
    onStatusChange?: (taskId: number, status: string) => void;
}

const TaskCard: React.FC<TaskCardProps> = ({ 
    task, 
    onEdit, 
    onDelete, 
    onStatusChange 
}) => {
    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'urgent': return 'text-red-600 bg-red-50';
            case 'high': return 'text-orange-600 bg-orange-50';
            case 'medium': return 'text-yellow-600 bg-yellow-50';
            case 'low': return 'text-green-600 bg-green-50';
            default: return 'text-gray-600 bg-gray-50';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'completed': return <CheckCircle2 className="h-4 w-4 text-green-500" />;
            case 'in_progress': return <Clock className="h-4 w-4 text-blue-500" />;
            case 'cancelled': return <AlertCircle className="h-4 w-4 text-red-500" />;
            default: return <Circle className="h-4 w-4 text-gray-400" />;
        }
    };

    const handleEdit = () => {
        if (onEdit) {
            onEdit(task.id);
        }
    };

    const handleDelete = () => {
        if (onDelete) {
            if (confirm('Are you sure you want to delete this task?')) {
                onDelete(task.id);
                showToast.success('Task deleted successfully');
            }
        }
    };

    const handleStatusChange = (newStatus: string) => {
        if (onStatusChange) {
            onStatusChange(task.id, newStatus);
            showToast.info(`Task status changed to ${newStatus}`);
        }
    };

    return (
        <div className="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow">
            <div className="flex items-start justify-between">
                <div className="flex items-center space-x-2">
                    {getStatusIcon(task.status)}
                    <h3 className="text-lg font-semibold text-gray-900">{task.title}</h3>
                </div>
                
                <div className="flex items-center space-x-2">
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${getPriorityColor(task.priority)}`}>
                        {task.priority}
                    </span>
                    
                    <div className="flex items-center space-x-1">
                        {onEdit && (
                            <button 
                                onClick={handleEdit}
                                className="p-1 text-gray-400 hover:text-blue-500 rounded"
                                title="Edit task"
                            >
                                <Edit3 className="h-4 w-4" />
                            </button>
                        )}
                        
                        {onDelete && (
                            <button 
                                onClick={handleDelete}
                                className="p-1 text-gray-400 hover:text-red-500 rounded"
                                title="Delete task"
                            >
                                <Trash2 className="h-4 w-4" />
                            </button>
                        )}
                        
                        <button className="p-1 text-gray-400 hover:text-gray-600 rounded">
                            <MoreHorizontal className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            {task.description && (
                <p className="mt-2 text-gray-600 text-sm">{task.description}</p>
            )}

            <div className="mt-4 flex items-center justify-between text-sm text-gray-500">
                <div className="flex items-center space-x-4">
                    {task.user && (
                        <div className="flex items-center space-x-1">
                            <User className="h-4 w-4" />
                            <span>Created by {task.user.name}</span>
                        </div>
                    )}
                    
                    {task.assigned_user && (
                        <div className="flex items-center space-x-1">
                            <User className="h-4 w-4" />
                            <span>Assigned to {task.assigned_user.name}</span>
                        </div>
                    )}
                </div>
                
                {task.due_date && (
                    <div className="flex items-center space-x-1">
                        <Calendar className="h-4 w-4" />
                        <span>Due {new Date(task.due_date).toLocaleDateString()}</span>
                    </div>
                )}
            </div>

            <div className="mt-2 flex space-x-2">
                {task.status !== 'completed' && (
                    <button
                        onClick={() => handleStatusChange('completed')}
                        className="text-xs px-2 py-1 bg-green-100 text-green-800 rounded hover:bg-green-200"
                    >
                        Mark Complete
                    </button>
                )}
                
                {task.status !== 'in_progress' && task.status !== 'completed' && (
                    <button
                        onClick={() => handleStatusChange('in_progress')}
                        className="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200"
                    >
                        Start Progress
                    </button>
                )}
            </div>
        </div>
    );
};

export default TaskCard;
