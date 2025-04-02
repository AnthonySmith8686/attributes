<?php

// --- PHP 8+ is required for attributes and typed properties ---

// -----------------------------
// Attribute Definitions
// -----------------------------

#[Attribute]
class Required
{
    // Marks a property as required (non-empty)
}

#[Attribute]
class MaxLength
{
    public function __construct(public int $length)
    {
    }
    // Ensures string length is <= $length
}

#[Attribute]
class MinLength
{
    public function __construct(public int $length)
    {
    }
    // Ensures string length is >= $length
}

// -----------------------------
// Example Data Class (Model)
// -----------------------------

class User
{
    #[Required]
    #[MaxLength(20)]
    #[MinLength(5)]
    public string $username;

    #[MaxLength(10)]
    public string $role;

    public function __construct(string $username, string $role)
    {
        $this->username = $username;
        $this->role = $role;
    }
}

// -----------------------------
// Validator Class
// -----------------------------

class Validator
{
    /**
     * Validate an object using attribute metadata.
     * Returns an array of validation error messages.
     */
    public static function validate(object $object): array
    {
        $errors = [];

        // Reflect on the object
        $reflection = new ReflectionObject($object);

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true); // allows reading private/protected props
            $value = $property->getValue($object);
            $name = $property->getName();

            // Loop over each attribute on the property
            foreach ($property->getAttributes() as $attr) {
                $attribute = $attr->newInstance();

                // Required validation
                if ($attribute instanceof Required) {
                    if (is_null($value) || $value === '') {
                        $errors[] = "$name is required.";
                    }
                }

                // Max length validation
                if ($attribute instanceof MaxLength) {
                    if (strlen($value) > $attribute->length) {
                        $errors[] = "$name must be at most {$attribute->length} characters.";
                    }
                }

                // Min length validation
                if ($attribute instanceof MinLength) {
                    if (strlen($value) < $attribute->length) {
                        $errors[] = "$name must be at least {$attribute->length} characters.";
                    }
                }
            }
        }

        return $errors;
    }
}

// -----------------------------
// Example Usage
// -----------------------------

$user = new User('jo', 'superadmin'); // too short and too long!

$errors = Validator::validate($user);

if ($errors) {
    echo "Validation errors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
} else {
    echo "All fields are valid.\n";
}
