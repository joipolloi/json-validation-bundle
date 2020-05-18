# JSON Validation Bundle

![Test Status](https://github.com/mrsuh/json-validation-bundle/workflows/Tests/badge.svg)

A Symfony bundle that provides an annotation to validate request/response JSON against a schema.

## Installation

```bash
composer require mrsuh/json-validation-bundle
```

## Usage

JsonSchema/Request/myAction.json
```json
{
    "description": "Request JSON schema",
    "type": "object",
    "properties": {
        "test": {
            "type": "string",
            "minLength": 1
        }
    },
    "required": [ "test" ]
}
```

JsonSchema/Response/myAction.json
```json
{
    "description": "Response JSON schema",
    "type": "object",
    "properties": {
        "test": {
            "type": "string",
            "minLength": 1
        }
    },
    "required": [ "test" ]
}
```

Controller/MyController.php
```php
use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonRequest;
use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class MyController
{
    /**
     * @ValidateJsonRequest("JsonSchema/Request/myAction.json", methods={"POST"}, emptyIsValid=true)
     * @ValidateJsonResponse("JsonSchema/Response/myAction.json", statuses={"200"}, emptyIsValid=true)
     */
    public function myAction(array $validJson): JsonResponse
    {
        return new JsonResponse($validJson);
    }
}
```

### Invalid JSON passed to request

*and request and exception listeners enabled*
```json
{
    "detail": "There was a problem with the JSON that was sent with the request",
    "errors": [
        {
            "constraint": "minLength",
            "context": 1,
            "message": "Must be at least 1 characters long",
            "minLength": 1,
            "pointer": "/test",
            "property": "test"
        }
    ],
    "status": 400,
    "title": "Unable to parse/validate JSON"
}
```

```bash
app.ERROR: Json request validation {"uri":"http://127.0.0.1:8000/my","schemaPath":"JsonSchema/Request/myAction.json","errors":[{"property":"test","pointer":"/test","message":"Must be at least 1 characters long","constraint":"minLength","context":1,"minLength":1}]} []
```

### Invalid JSON passed to response

*and response listener enabled*
```bash
app.WARNING: Json response validation {"uri":"http://127.0.0.1:8000/my","schemaPath":"JsonSchema/Response/myAction.json","errors":[{"property":"test","pointer":"/test","message":"Must be at least 1 characters long","constraint":"minLength","context":1,"minLength":1}]} []
```

## Configuration

```yaml
mrsuh_jsonvalidation:
    enable_request_listener: true
    enable_response_listener: true
    enable_exception_listener: true
```

## Single validator usage
```php
use Mrsuh\JsonValidationBundle\JsonValidator\JsonValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyController
{
    public function myAction(Request $request, JsonValidator $validator): Response
    {
        $validator->validate($request->getContent(), 'JsonSchema/Request/myAction.json');
        $errors = $validator->getErrors();
        if(!empty($errors)) {
            // do something with errors
        }   
        
        return new Response();
    }
}
```