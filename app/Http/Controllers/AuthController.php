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

class AuthController extends Controller
{
    /**
     * Handle the login process.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'property_code' => 'required|string',
            'email'         => 'required|email',
            'password'      => 'required|string',
        ]);

        // Check if the user exists in the master database's admin_users table
        $adminUser  = AdminUser::where('email', $validated['email'])->first();
        $superAdmin = $adminUser && $adminUser->role === 'super_admin';

        if ($adminUser) {
            // Authenticate the admin user

            // if the property code provided is correct and the admin_user id matches the owner_id on the property whose property_code is provided
            $correctProperty = Property::where('property_code', $validated['property_code'])->where('owner_id', $adminUser->id)->first();
            if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']]) && $correctProperty) {
                if(DatabaseService::switchConnection($validated['property_code'])){
                    return response()->json([
                        'message' => 'Login successful',
                        'user'    => Auth::user(),
                        'token'   => Auth::user()->createToken('API Token')->plainTextToken,
                    ]);
                } else {
                    return response()->json(['error' => 'Could not switch']);
                }
            }
            return response()->json(['error' => 'Invalid credentials for admin'], 401);
        } else if ($superAdmin) {
            // Authenticate the super admin user
            if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
                return response()->json([
                    'message' => 'Login successful',
                    'user'    => Auth::user(),
                    'token'   => Auth::user()->createToken('API Token')->plainTextToken,
                ]);
            }
            return response()->json(['error' => 'Invalid credentials for super admin'], 401);
        } else {
            // If the user is not found in the master database, check the property database
            try {
                // Switch the database connection based on the property code
                DatabaseService::switchConnection($validated['property_code']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Property not found.'], 404);
            }
        }

        // Attempt to authenticate the user in the property-specific database
        if (Auth::guard('property_user')->attempt([
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ])) {
            $propertyUser = Auth::guard('property_user')->user();

            return response()->json([
                'message' => 'Login successful',
                'user'    => $propertyUser,
                'token'   => $propertyUser->createToken('API Token')->plainTextToken,
            ]);
        }

        // If authentication fails, return an error response
        return response()->json(['error' => 'Unauthorized'], 401);
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
        // if product_code is provided, switch the database connection
        if ($request->has('product_code')) {
            $productCode = $request->product_code;
            DatabaseService::switchConnection($productCode);
        }

        try {
            DB::beginTransaction();

            $user = PropertyUser::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role'     => 'user',
                'status'   => 'active',
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

    public function logout(Request $request)
    {
        // Check if the user is authenticated via Sanctum (for API users)
        if (Auth::guard('admin_user')->check()) {
            // Admin user logged out, clear the token
            Auth::guard('admin_user')->logout();
            return response()->json([
                'message' => 'Admin user logged out successfully!',
            ], 200);
        } else if (Auth::guard('property_user')->check()) {
            // Property user logged out, clear the token
            Auth::guard('property_user')->logout();
            return response()->json([
                'message' => 'Property user logged out successfully!',
            ], 200);
        }

        // In case there's no authentication guard matched
        return response()->json([
            'message' => 'No authenticated user found.',
        ], 401);
    }

    // Method to create the property-specific database
    protected function createPropertyDatabase($propertyName)
    {
        // Normalize the property name to create a valid database name
        // Replace spaces with underscores and convert to lowercase
        $normalizedPropertyName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $property->property_name)));

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
