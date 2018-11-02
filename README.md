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

Use composer: `composer require kyrylich/json-valid-bundle`

Open `AppKernel.php` in your Symfony project:

```php
$bundles = array(
    // ...
    new JoiPolloi\Bundle\JsonValidationBundle\JsonValidationBundle(),
    // ...
);
```

## Configuration

The only configuration option is whether to enable the application/problem+json event listener. This is described in detail below, it defaults to off, but can be enabled with the following configuration in your config.yml:

```yml
joipolloi_jsonvalidation:
    enable_problemjson_listener: true
```

## Details

Behind the scenes the bundle registers an event listener on the `kernel.controller` event that will validate the request content (i.e. `$request->getContent();`) against a JSON schema using the [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema) library.

If there is an issue locating the JSON schema, decoding the JSON, decoding the JSON schema or validating against the JSON, a [JsonValidationException](Exception/JsonValidationException.php) (which extends BadRequestHttpException) is thrown with an error message.

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

If you want the decoded JSON as an associative array or use the [Symfony form component](http://symfony.com/doc/current/forms.html), type hint `$validJson` as an array:

```php
/**
 * @ValidateJson("@MyBundle/Resources/schema/action-schema.json")
 */
public function myAction(array $validJson)
{
    $form = $this->createForm(MyFormType::class);
    $form->submit($validJson);

    if ($form->isValid()) {
        // ...
    }
}
```

This does incur a slight performance overhead versus getting an object as the JSON needs to be decoded twice: once to validate against the JSON schema and again as an associative array. If your JSON is large but only a single level deep then you may get better performance by just casting to an array:

```php
public function myAction($validJson)
{
    // ...
    $form->submit((array)$validJson);
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

## application/problem+json responses

An exception listener is included within the bundle that can send an `application/problem+json` response as detailed in [RFC 7807](https://tools.ietf.org/html/rfc7807). The listener is turned off by default to allow for your own application to handle the exception but can be turned on with configuration in your config.yml file:

```yml
joipolloi_jsonvalidation:
    enable_problemjson_listener: true
```

If the listener is disabled, a 400 bad request exception is thrown and caught as per your application. If turned on and there is a problem decoding or validating the JSON, a response might look like:

```json
{
    "status": 400,
    "title": "Unable to parse\/validate JSON",
    "detail": "There was a problem with the JSON that was sent with the request",
    "errors": [
        {
            "message": "[4] Syntax error"
        }
    ]
}
```

The "errors" key will be an array of at least one error. Each error will be an object with at least a "message" key, but may additionally have "constraint", "pointer" and "property" keys with useful information.

While errors within this array should be safe to send back to the client, there may be some information leakage with regards paths - either to the schema or referenced files. If in doubt, disable the listener and roll your own to have more control.
