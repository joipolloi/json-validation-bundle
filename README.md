# JSON Validation Bundle

[![Build Status](https://api.travis-ci.org/joipolloi/json-validation-bundle.svg)](https://travis-ci.org/joipolloi/json-validation-bundle)

A Symfony bundle that provides an annotation to validate JSON passed to a controller action against a schema.

## Usage

When creating a controller method that accepts JSON as input (e.g. a POST method), put the `@ValidateJson` annotation on your action and point to the schema to validate against.

```php
use JoiPolloi\Bundle\JsonValidationBundle\Annotation\ValidateJson

class MyController
{
    /**
     * @ValidateJson("@MyBundle/Resources/schema/action-schema.json")
     */
    public function myAction()
    {
        // ...
    }
}
```

Now any time the action is called, the passed JSON will be validated against the schema. If there are no validation errors, the action will execute as normal. If there are errors then a 400 (bad request) response will be returned.

## Installation

Use composer: `composer require joipolloi/json-validation-bundle`

Open `AppKernel.php` in your Symfony project:

```php
$bundles = array(
    // ...
    new JoiPolloi\Bundle\JsonValidationBundle\JsonValidationBundle(),
    // ...
);
```

## Configuration

No further configuration is required or provided.

## Details

Behind the scenes the bundle registers an event listener on the `kernel.controller` event that will validate the request content (i.e. `$request->getContent();`) against a JSON schema using the [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema) library.

If there is an issue locating the JSON schema, decoding the JSON, decoding the JSON schema or validating against the JSON, a BadRequestHttpException is thrown with an error message.

## Options

### Getting the valid JSON

In order to save time and processing, you can get the validated JSON as an object by getting the `validJson` attribute on a request, or by putting `$validJson` as an argument to your action:

```php
/**
 * @ValidateJson("@MyBundle/Resources/schema/action-schema.json")
 */
public function myAction(Request $request, $validJson)
{
    // $request->attributes->get('validJson') === $validJson
}
```

If you use the [Symfony form component](http://symfony.com/doc/current/forms.html) you can cast the `$validJson` object to an array and supply it directly to the form as if handling a standard request:

```php
/**
 * @ValidateJson("@MyBundle/Resources/schema/action-schema.json")
 */
public function myAction($validJson)
{
    $form = $this->createForm(MyFormType::class);
    $form->submit((array)$validJson);

    if ($form->isValid()) {
        // ...
    }
}
```

### Specifying the HTTP methods to validate upon

If your controller action listens on multiple HTTP methods (e.g. PUT and POST) and you only want to validate the JSON on one of them, you can use the `methods` parameter to the annotation:

```php
/**
 * @ValidateJson("@MyBundle/Resources/schema/action-schema.json", methods={"POST"})
 */
public function myAction(Request $request, $validJson = null)
{
    if ($request->isMethod('POST')) {
        // $validJson !== null
    }
}
```

### Allowing for empty as a valid value

If for some reason you want to allow empty content to also be valid, use the `emptyIsValid` parameter to the annotation:

```php
/**
 * @ValidateJson("@MyBundle/Resources/schema/action-schema.json", emptyIsValid=true)
 */
public function myAction($validJson = null)
{
    // ...
}
```

Note that only empty request content will be classed as valid; if empty but syntactically valid JSON is passed, this will still be validated against the schema (i.e. "{}" will not be counted as empty).
