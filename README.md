# JSON Validation Bundle

![Test Status](https://github.com/mrsuh/json-validation-bundle/workflows/Tests/badge.svg)

A Symfony bundle that provides an annotation to validate request/response JSON against a schema.

## Installation

```bash
composer require mrsuh/json-validation-bundle
```

## Usage

MyBundle/JsonSchema/Request/myAction.json
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

MyBundle/JsonSchema/Response/myAction.json
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

MyBundle/Controller/MyController.php
```php
use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonRequest;
use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class MyController
{
    /**
     * @ValidateJsonRequest("@MyBundle/JsonSchema/Request/myAction.json", methods={"POST"}, emptyIsValid=true)
     * @ValidateJsonResponse("@MyBundle/JsonSchema/Response/myAction.json", statuses={"200"}, emptyIsValid=true)
     */
    public function myAction(array $validJson): JsonResponse
    {
        return new JsonResponse($validJson);
    }
}
```

## Configuration

```yaml
mrsuh_jsonvalidation:
    enable_request_listener: true
    enable_response_listener: true
    enable_exception_listener: true
```