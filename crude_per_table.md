# Crude Per Table provided

Prerequisites:

1. Provide database table schema details
2. Specify any unique business logic requirements

Code Generation Checklist:

Models - Model folder: app/Models/\*

    -   Extends Eloquent Model
    -   Implements Auditable
    -   Generate comprehensive scope methods based on the table
    -   Define fillable/guarded properties
    -   Establish model relationships
    -   Include soft delete capability
    -   for all model: template be class ModalName extends Model implements Auditable
        {
            use HasFactory;
            use \OwenIt\Auditing\Auditable;

            ...
        } will necessay imports like:
    -   use Illuminate\Database\Eloquent\Factories\HasFactory;
    -   use Illuminate\Database\Eloquent\Model;
    -   use Illuminate\Support\Facades\DB;
    -   use OwenIt\Auditing\Contracts\Auditable;

Controllers - Controller folder: app/Http/Controllers/\*

    -   Extends BasicController
    -   Implement full RESTful API methods with error handling
        -   for every method return:
            return $this->sendResponse($data, 'Success message'); or
            return $this->sendError($e->getMessage()); and
            use DB::beginTransaction();
        -   index():
            -   Filtered, paginated results
        -   store():
            -   Validated resource creation
        -   show():
            -   Single resource retrieval
        -   update():
            -   Validated resource modification
        -   destroy():
            -   Resource deletion
            -   Soft delete support
        -   summary():
            -   Aggregate metrics
            -   Total count
            -   Counts per status
            -   Total amount calculations

Requests - Form Requests folder: app/Http/Requests/\*

    -   Comprehensive validation rules
    -   Custom validation messages

Additional Considerations:

    -   named modal  based on table_name snake_case
    -   same for controller should be equal to the table name but snake_case
    -   Add robust error handling
    -   Ensure data integrity
    -   Follow Laravel best practices and coding standards
    -   Use DB transactions for all create/update operations
    -   Consistent error response using sendError()
    -   Consistent success response using sendResponse()

Action Required:

Ask for table schema if not provided

Very important - put the generated files in provided path above - if there is existing file write ontop of it
