<?php
namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Modules\Property\Models\Property;
use App\Models\PropertyUser;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Modules\Property\Models\Room;

class AuthController extends Controller
{
    /**
     * Handle the login process.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function fetchPropertyUsers()
    {
        //if a
    }
    
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
                'property_code' => 'nullable|string',
            ]);
            $guards = ['admin_user', 'property_user'];
            try {
                foreach ($guards as $guard) {
                    if ($guard === 'property_user') {
                        $propertyCode = $validated['property_code'];
                        if ($propertyCode) {
                            DatabaseService::switchConnection($propertyCode);
                        } else {
                            return response()->json([
                                'error' => 'Property not found for the given user.',
                            ], 404);
                        }
                    }
                    if (Auth::guard($guard)->attempt([
                        'email'    => $validated['email'],
                        'password' => $validated['password'],
                    ])) {
                        $user = Auth::guard($guard)->user();
                        Log::info("{$guard} login successful: {$user->email}");

                        return response()->json([
                            'message' => 'Login successful',
                            'guard'   => $guard, // Indicating which guard was used
                            'user'    => $user,
                            'token'   => $user->createToken(ucfirst($guard) . ' API Token')->plainTextToken,
                        ], 200);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error during login attempt: ' . $e->getMessage());

                return response()->json([
                    'error'   => 'An error occurred during login.',
                    'details' => $e->getMessage(),
                ], 500);
            }
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An error occurred during login.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        //if user email given and the property name provided matches a combination that is already in the database, return error that the apartment with the owner is already existing
        if ($request->has('email') && $request->has('property_name')) {
            $email        = $request->email;
            $propertyName = $request->property_name;
            $user         = AdminUser::where('email', $email)->first();
            $property     = Property::where('property_name', $propertyName)->first();

            if ($user && $property) {
                return response()->json([
                    'error' => 'User with the same email and property name already exists.',
                ], 400);
            }
        }

        //ig password and confirm password do not match
        $password        = $request->password;
        $confirmPassword = $request->password_confirmation;
        if ($password != $confirmPassword) {
            return response()->json([
                'error' => 'Please ensure your password field matches with the confirm password field',
            ], 400);
        }
        // Start a transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Generate the random property code:
            $propertyCode = Str::upper(Str::random(1)) . '-' . rand(100, 999);

            // Create the property in the master database
            $property = Property::create([
                'property_name'    => $request->property_name,
                'property_code'    => $propertyCode, // Use the generated code here
                'property_address' => $request->property_address,
                'owner_id'         => null, // Will update owner_id after creating the user
            ]);

            // Create the admin user in the master database
            $user = AdminUser::create([
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
                'role'        => 'admin',
                'property_id' => $property->id, // Link the user to the property
            ]);

            // Update the property owner_id after user creation
            $property->owner_id = $user->id;
            $property->save(); // Save the updated owner_id

            // Commit the transaction after successful operations
            DB::commit();

            // Create the property-specific database
            $this->createPropertyDatabase($property->property_name); // Use the property name for the database

            // Run the property-specific migrations
            $this->runPropertyMigrations($property->property_name);

            // Return success response
            return response()->json([
                'message'    => 'Admin and property created successfully!',
                'admin_user' => $user,
                'property'   => $property,
            ], 201);
        } catch (\Exception $e) {
            // Rollback if something goes wrong
            DB::rollBack();

            return response()->json([
                'error'   => 'Something went wrong. Please try again.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerSuperAdmin(Request $request)
    {
        // Start a transaction to ensure data integrity
        DB::beginTransaction();

        //the super admin user is created in the master database and has no property

        try {
            // Create the super admin user in the master database
            $user = AdminUser::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role'     => 'super_admin', // Will update owner_id after creating the user
            ]);

            // Commit the transaction after successful operations
            DB::commit();

            // Return success response
            return response()->json([
                'message'     => 'Super Admin created successfully!',
                'super_admin' => $user,
                'token'       => $user->createToken('API Token')->plainTextToken,
            ], 201);
        } catch (\Exception $e) {
            // Rollback if something goes wrong
            DB::rollBack();
            return response()->json([
                'error'   => 'Something went wrong. Please try again.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // function to register a new user in the property database
    public function registerUser(Request $request)
    {
        if ($request->has('email') && $request->has('room_id')) {
            $email = $request->email;
            $room  = Room::find($request->room_id);
            $user  = PropertyUser::where('email', $email)->first();

            if ($user && $room) {
                return response()->json([
                    'error' => 'User with the same email and room already exists.',
                ], 400);
            }
        }
        try {
            DB::beginTransaction();

            $user = PropertyUser::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role'     => $request->role?? 'user',
                'status'   => 'active',
                'room_id'  => $request->room_id,
            ]);

            // Commit the transaction after successful operations
            DB::commit();

            return response()->json([
                'message' => 'User created successfully!',
                'user'    => $user,
            ], 201);
        } catch (\Exception $e) {
            // Rollback if something goes wrong
            DB::rollBack();
            //if user account with same email already exists, return error

            return response()->json([
                'error'   => 'Something went wrong. Please try again.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchPropertyTenants ()
    {
        $tenants = PropertyUser::where('role', 'user')->get();

        return response()->json(['tenants' => $tenants], 200);
    }

    public function fetchPropertyStaffs ()
    {
        $staffs = PropertyUser::where('role', 'staff')->get();

        return response()->json(['staffs' => $staffs], 200);
    }

    //function to update property user details
    public function updatePropertyUser(Request $request, $id)
    {
        $user = PropertyUser::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->update([
            'name'     => $request->name ?? $user->name,
            'email'    => $request->email ?? $user->email,
            'phone'    => $request->phone ?? $user->phone,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role'     => $request->role ?? $user->role,
            'status'   => $request->status ?? $user->status,
            'room_id'  => $request->room_id ?? $user->room_id,
        ]);

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin_user')->check()) {
            $user = Auth::guard('admin_user')->user();
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete(); // Revoke API tokens
            }
            Auth::guard('admin_user')->logout();
        
            return response()->json(['message' => 'Admin user logged out successfully!'], 200);
        }
    
        if (Auth::guard('property_user')->check()) {
            $user = Auth::guard('property_user')->user();
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete(); // Revoke API tokens
            }
            Auth::guard('property_user')->logout();
        
            return response()->json(['message' => 'Property user logged out successfully!'], 200);
        }
    
        return response()->json(['message' => 'No authenticated user found.'], 401);
    }

    // Method to create the property-specific database
    protected function createPropertyDatabase($propertyName)
    {
        // Normalize the property name to create a valid database name
        // Replace spaces with underscores and convert to lowercase
        $normalizedPropertyName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $propertyName)));

        // Ensure the database name is valid (replace any other invalid characters if needed)
        $dbName = $normalizedPropertyName;

        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to create property database.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run property-specific migrations.
     */
    protected function runPropertyMigrations($propertyName)
    {
        // property code is gotten from the property_code column in the properties table
        $propertyCode = Property::where('property_name', $propertyName)->value('property_code');
        // Switch the database connection based on the property code
        DatabaseService::switchConnection($propertyCode);

        // Purge and reconnect to ensure the correct database connection is used
        DB::purge('property');     // Purge any existing connections for 'property'
        DB::reconnect('property'); // Reconnect with the new configuration

        // Ensure the migrations table exists before running the migrations
        try {
            // This will create the migrations table if it doesn't exist
            Artisan::call('migrate:install', ['--database' => 'property']);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to create migrations table.',
                'details' => $e->getMessage(),
            ], 500);
        }

        // Run all migrations from the property-specific directory
        try {
            // This will run all migrations in the 'property_specific' directory
            Artisan::call('migrate', [
                '--database' => 'property',
                '--path'     => 'database/migrations/property_specific', // Specify the migration directory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to run migrations.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function migrateAllProperties()
    {
        $properties = Property::all();

        foreach ($properties as $property) {
            $normalizedPropertyName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $property->property_name)));

            config(['database.connections.property' => [
                'driver'   => 'mysql',
                'host'     => env('DB_HOST'),
                'port'     => env('DB_PORT'),
                'database' => $normalizedPropertyName,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
            ]]);

            DB::purge('property');
            DB::reconnect('property');

            try {
                Artisan::call('migrate', [
                    '--database' => 'property',
                    '--path'     => 'database/migrations/property_specific',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error'   => 'Failed to run migrations for ' . $property->property_name,
                    'details' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json(['message' => 'Migrations run for all properties']);
    }
}
