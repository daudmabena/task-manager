# Slug Implementation for Systems

## Overview

The Systems model now includes automatic slug generation functionality using the `cviebrock/eloquent-sluggable` package. Slugs are URL-friendly versions of the system names that are used in routes instead of IDs.

## Features Implemented

### 1. **Automatic Slug Generation**
- Slugs are automatically generated from the system name when creating or updating
- Example: "Financial Management System" → "financial-management-system"

### 2. **Unique Slug Enforcement**
- Duplicate slugs are automatically handled by appending numbers
- Example: If "test-system" exists, new system with same name gets "test-system-1"

### 3. **Slug-based Routing**
- All system routes now use slugs instead of IDs
- URLs look like `/systems/financial-management-system` instead of `/systems/1`

### 4. **Update Behavior**
- Slugs automatically update when system names change
- Preserves URL structure while keeping them readable

## Technical Implementation

### Database Changes
- Added `slug` column to `systems` table with unique constraint
- Column is placed after `name` for logical organization

### Model Configuration
```php
// System.php
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;

class System extends Model
{
    use Sluggable, SluggableScopeHelpers;
    
    protected $fillable = ['name', 'slug', 'description', ...];
    
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
                'separator' => '-',
                'includeTrashed' => true,
                'onUpdate' => true,
            ]
        ];
    }
    
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

### Route Configuration
```php
// web.php
Route::resource('systems', SystemsController::class)->parameters([
    'systems' => 'system:slug'
]);
```

### Controller Updates
- No changes required to controller methods
- Laravel automatically resolves models using slug through route model binding
- Validation updated to ensure name uniqueness

### Request Validation
```php
// StoreSystemRequest.php
'name' => ['required', 'string', 'max:255', 'unique:systems,name'],

// UpdateSystemRequest.php  
'name' => ['required', 'string', 'max:255', 'unique:systems,name,' . $system->id],
```

## Usage Examples

### Creating a System
```php
$system = System::create([
    'name' => 'Customer Management System',
    'description' => 'Manages customer data',
    'created_by' => auth()->id(),
    'updated_by' => auth()->id(),
]);

// Automatically gets slug: 'customer-management-system'
echo $system->slug; // customer-management-system
```

### Updating a System
```php
$system = System::first();
$system->update(['name' => 'Updated System Name']);

// Slug automatically updates to: 'updated-system-name'
echo $system->slug; // updated-system-name
```

### Finding by Slug
```php
// Using the slug scope helper
$system = System::findBySlug('financial-management-system');

// Or standard where clause
$system = System::where('slug', 'financial-management-system')->first();
```

### Route URLs
All system routes now use slugs:

- **Index**: `/systems`
- **Show**: `/systems/financial-management-system`
- **Edit**: `/systems/financial-management-system/edit`
- **Update**: `PATCH /systems/financial-management-system`
- **Delete**: `DELETE /systems/financial-management-system`

## Artisan Commands

### Generate Slugs for Existing Systems
```bash
# Generate slugs for systems that don't have them
php artisan systems:generate-slugs

# Force regenerate all slugs  
php artisan systems:generate-slugs --force
```

## Benefits

### 1. **SEO Friendly URLs**
- URLs are more descriptive and search engine friendly
- Better user experience with readable URLs

### 2. **Security**
- Prevents exposure of internal IDs
- Makes it harder to enumerate resources

### 3. **Stability**
- Slugs are more stable than IDs for bookmarking
- Better for API consumers

### 4. **User Experience**
- Users can understand what the URL points to
- More professional appearance

## Migration Path

For existing systems:
1. ✅ Migration added `slug` column
2. ✅ Command generated slugs for existing records
3. ✅ Model configured with Sluggable trait
4. ✅ Routes updated to use slug parameter
5. ✅ Validation updated for uniqueness

## Testing

All functionality has been tested:

- ✅ Automatic slug generation on create
- ✅ Automatic slug update on name change
- ✅ Unique slug handling with numeric suffixes
- ✅ Route model binding with slugs
- ✅ Finding systems by slug
- ✅ Existing systems slug generation

## Notes

- Slugs are case-insensitive and URL-safe
- Special characters are removed/replaced with hyphens
- The `includeTrashed: true` option ensures unique slugs even considering soft-deleted records
- The `onUpdate: true` option allows slugs to be regenerated when the source field changes

The implementation provides a robust, SEO-friendly URL structure while maintaining backwards compatibility through the migration and slug generation command.
