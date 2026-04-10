# PHP DTO Package

[![Tests](https://github.com/GaiPalyan/dtoforge/actions/workflows/tests.yml/badge.svg)](https://github.com/GaiPalyan/dtoforge/actions/workflows/tests.yml)

A flexible and powerful Data Transfer Object (DTO) library for PHP that provides validation, type casting, default value generation, and convenient data manipulation methods.

## Features

- **Easy DTO Creation** - Create DTOs from arrays, JSON, or use fluent setters
- **Built-in Validation** - Validate data with custom rules (required fields, INN validation, etc.)
- **Type Casting** - Automatic type conversion for scalar values
- **Data Manipulation** - Merge, diff, fill, and clone DTOs
- **Nested DTOs** - Full support for nested DTO structures
- **Default Values** - Generate default values automatically when needed
- **Serialization** - Convert to arrays and JSON easily

## Installation

```bash
composer require gpalyan/dto-forge
```

## Basic Usage

### Creating DTOs

All DTOs must extend `BaseDto`:

```php
use Ru\One2Work\Php\DtoValidator\Support\BaseDto;

final class SimpleDto extends BaseDto
{
    public ?string $name = null;
    public ?string $age = null;
    public ?string $address = null;
}

// From array
$dto = new SimpleDto([
    'name' => 'John Doe',
    'age' => '25',
    'address' => 'Some address'
]);

// From JSON
$dto = new SimpleDto(json_encode($data));

// Using setters
$dto = new SimpleDto()
    ->setName('John Doe')
    ->setAge('25')
    ->setAddress('Some address');

// Access via properties or getters
echo $dto->name;           // John Doe
echo $dto->getName();      // John Doe

if ($dto->hasName()) {
    echo "Name is set";
}

```


### Magic Methods

DTOs automatically provide getters, setters, and checkers for all properties without explicit method definitions.
You don't need to manually create getter/setter methods - they are generated automatically based on property names using camelCase convention.

### Filling Data

```php
$dto = new SimpleDto();
$dto->fill([
    'name' => 'John Doe',
    'age' => '25'
]);

// Overwrite specific fields
$dto->fill(['name' => 'Jane Doe']);
echo $dto->name; // Jane Doe
```

### Merging DTOs

```php
$dto1 = new SimpleDto()->setName('John Doe');
$dto2 = new SimpleDto()
    ->setAge('25')
    ->setAddress('Some address');

$dto3 = $dto1->merge($dto2);

// $dto3 now contains all fields from both DTOs
echo $dto3->getName();    // John Doe
echo $dto3->getAge();     // 25
echo $dto3->getAddress(); // Some address
```

### Comparing DTOs

```php
$dto1 = new SimpleDto([
    'name' => 'John Doe',
    'age' => '25',
    'address' => 'Some address'
]);

$dto2 = new SimpleDto(['name' => 'Jane Doe']);

$diff = $dto1->diff($dto2);

/*
[
    'name' => ['old' => 'John Doe', 'new' => 'Jane Doe'],
    'age' => ['old' => '25', 'new' => null],
    'address' => ['old' => 'Some address', 'new' => null]
]
*/
```

### Cloning DTOs

```php
$dto = new SimpleDto(['name' => 'John Doe']);
$clone = $dto->clone();

$diff = $dto->diff($clone); // Empty array - perfect copy
```

### Serialization

```php
$dto = new SimpleDto([
    'name' => 'John Doe',
    'age' => '25'
]);

// To array
$array = $dto->toArray();

// To JSON
$json = $dto->toJson();
```

## Nested DTOs

```php
use YourVendor\Dto\NestedDto;
use YourVendor\Dto\SimpleDto;

$dto = new NestedDto([
    'children' => new SimpleDto([
        'name' => 'John Doe',
        'age' => '25',
        'address' => 'Some address'
    ]),
    'companyAddress' => 'Some address',
    'companyName' => 'google'
]);

// Access nested properties
$children = $dto->getChildren(); // Returns SimpleDto instance
echo $children->getName(); // John Doe

// Serialize nested DTOs
$array = $dto->toArray();
/*
[
    'children' => [
        'name' => 'John Doe',
        'age' => '25',
        'address' => 'Some address'
    ],
    'companyAddress' => 'Some address',
    'companyName' => 'google'
]
*/

// Merge nested DTOs
$dto1 = new NestedDto($data);
$dto2 = new NestedDto();
$dto2->setChildren(
    (new SimpleDto())
        ->setName('Changed name')
        ->setAge(null)
);

$merged = $dto1->merge($dto2);
echo $merged->getChildren()->getName(); // Changed name
```

## Validation

Validation is applied via PHP attributes on DTO properties.

### Using Built-in Validators

```php
use Ru\One2Work\Php\DtoValidator\Support\BaseDto;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Uuid;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Required;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Inn;

final class UserDto extends BaseDto
{
    #[Uuid]
    public ?string $id = null;
    
    #[Required]
    public ?string $name = null;
    
    #[Inn]
    public ?string $inn = null;
}

// Validation happens automatically on property assignment
$dto = new UserDto();
$dto->setId('invalid-uuid'); // Throws DtoValidationException
$dto->setId('550e8400-e29b-41d4-a716-446655440000'); // ✅

$dto->setInn('627708638650'); // ✅ Valid individual INN
$dto->setInn('4404380820');   // ✅ Valid legal entity INN
```
### Validation Errors

Get all validation errors that occurred during DTO population:

```php
$dto = new UserDto();

try {
    $dto->setId('invalid-uuid');
} catch (DtoValidationException $e) {
    // Exception thrown
}

// Validation errors are used by default value generators
if (empty($dto->getValidationErrors())) {
    // Safe to generate defaults
}
```

### Creating Custom Validators

Implement `PropertyValidatorInterface` to create your own validators:

```php
use Attribute;
use Ru\One2Work\Php\DtoValidator\Contracts\PropertyValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Traits\HasLaravelValidation;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Email implements PropertyValidatorInterface
{
    use HasLaravelValidation;

    public function validate(mixed $value, string $propertyName): void
    {
        $this->performValidation(
            value: $value,
            rules: ['nullable', 'email'],
            field: $propertyName
        );
    }
}

// Usage
final class ContactDto extends BaseDto
{
    #[Email]
    public ?string $email = null;
}
```

**Requirements for custom validators:**
- Must be a PHP 8 Attribute with `Attribute::TARGET_PROPERTY`
- Must implement `PropertyValidatorInterface`
- Throw `DtoValidationException` on validation failure

## Default Value Generation

Generate default values for properties using attributes.

### Creating Custom Generators

Implement `DefaultValueGeneratorInterface` to create your own generators:

```php
use Attribute;
use Ru\One2Work\Php\DtoValidator\Contracts\DefaultValueGeneratorInterface;
use Ru\One2Work\Php\DtoValidator\Support\BaseDto;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UuidGenerator implements DefaultValueGeneratorInterface
{
    public function generate(BaseDto $dto): mixed
    {
        // return the generated value for the property
    }

    public function supports(BaseDto $dto, string $propertyName): bool
    {
        // return true if generation should occur (e.g. property is empty and DTO has no errors)
    }
}

// Usage
final class EntityDto extends BaseDto
{
    #[UuidGenerator]
    public ?string $id = null;
}
```

**Requirements for custom generators:**
- Must be a PHP 8 Attribute with `Attribute::TARGET_PROPERTY`
- Must implement `DefaultValueGeneratorInterface`
- Implement `generate(BaseDto $dto): mixed` - returns the generated value
- Implement `supports(BaseDto $dto, string $propertyName): bool` - determines if generation should occur



## Type Casting

The package automatically casts values to appropriate types:

```php
final class UserDto extends BaseDto
{
    public ?string $age = null; // declared as string
}

$dto = new UserDto()->setAge(25); // passing int
echo gettype($dto->getAge()); // "string" — automatically cast to match property type
```

## API Reference

### Core Methods

- `fill(array $data): self` - Fill DTO with data from array
- `merge(DtoInterface $dto): self` - Merge another DTO into this one
- `diff(DtoInterface $dto): array` - Get differences between DTOs
- `clone(): self` - Create a deep copy of the DTO
- `toArray(): array` - Convert DTO to array
- `toJson(): string` - Convert DTO to JSON string
- `generateDefaultsIfAllowed(): void` - Generate default values for null fields

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.