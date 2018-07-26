# WobbleCodeRestBundle

## Quick RESTFul Apis For Symofny 3 & 4

A bundle that creates RESTful Apis in seconds, based on your current controllers
actions just using annotations. This is very helpful if you need access to a
basic RESTful API under the same domain or page.

A practical Case: if you're using Backbone, you can progressive enhance the
functionality supporting common CRUD and One simple page CRUD enabling a quick
RESTFul API…

These are the main features:

+ Works with your current controllers
+ Works with your current Symfony forms
+ Intercepts current redirects
+ Version api support
+ Api based on content negotiation
+ Integrated with JMSSerializerBundle
+ Integrate with KnpPaginatorBundle to obtain metadata

[![Latest Stable Version](https://poser.pugx.org/wobblecode/rest-bundle/v/stable.svg)](https://packagist.org/packages/wobblecode/rest-bundle)
[![Total Downloads](https://poser.pugx.org/wobblecode/rest-bundle/downloads.svg)](https://packagist.org/packages/wobblecode/rest-bundle)
[![Travis](https://travis-ci.org/wobblecode/WobbleCodeRestBundle.svg?branch=feature%2Fsymfony4-support)](https://travis-ci.org/wobblecode/WobbleCodeRestBundle/builds)
[![License](https://poser.pugx.org/wobblecode/rest-bundle/license.svg)](https://packagist.org/packages/wobblecode/rest-bundle)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/2f4e1635-bf06-4f84-97e8-e7923a41274b.svg)](https://insight.sensiolabs.com/projects/2f4e1635-bf06-4f84-97e8-e7923a41274b)

### Quick Example

This is the most basic example of use. Just adding the Rest annotation, the api
will be available using content negotiation. So if you request JSON, it will
return a JSON format schema, If html is requested it will fallback in @Template
so the related view will be rendered.

It will use JSMSerializer if it's possible in the model as well.

    use WobbleCode\RestBundle\Configuration\Rest;

    /**
     * @Route("/")
     * @Template()
     * @Rest()
     */
    public function indexAction()
    {
        ...

        return array(
            'entities' => $entity
        );
    }

Example using [httpie](https://github.com/jakubroztocil/httpie)
(replace your URL if needed)

    http http://localhost:8000 --json

    HTTP/1.1 200 OK
    Cache-Control: max-age=0, must-revalidate, no-cache, no-store, public, s-maxage=0
    Connection: close
    Content-Type: application/json
    Host: localhost:8000
    X-Debug-Token: 243d5e
    X-Debug-Token-Link: /_profiler/243d5e
    X-Powered-By: PHP/5.5.7

    {
        "entities": [...],
        "metadata": {
            "count": 10,
            "items_per_page": 10,
            "page_number": 1,
            "total_count": 244
        }
    }


## Setup

### Add to composer

    "wobblecode/rest-bundle": "dev-master",

### Enable bundle in Kernel

    new WobbleCode\RestBundle\WobbleCodeRestBundle()

## Options

**All examples shows the default values.**

### output

List of default objects to serialize from the returned array.

    /**
     * @Rest(output={"entity", "entities", "meta"})
     */
    public function indexAction()
    {
        ...

        return array(
            'user'   => $user,
            'post'   => $post,
            'entity' => $entity
        );
    }

Only `entity` value will be serialized and returned.

### serializeGroups

List of groups to use with the serializer ( see JMSSerializerBundle groups ).
By default will serialize all groups if not defined

    /**
     * @Rest(serializeGroups={"api", "ui-admin"})
     */

### acceptedContent

List of accepted headers that enables REST

    /**
     * @Rest(acceptedContent={"application/json"})
     */

You will have to send `Accept: application/json` in order to enable the REST api
functionality. If there is no match, it will fallback to the controller view.

### defaultAccept

If Accept header is missing you can set a default value with defaultAccept

    /**
     * @Rest(defaultAccept="application/json")
     */

If the Accept header is missing the default value is
`text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8`

### payloadMapping

Defines if the payload is assigned to a POST value. This is useful because
forms are usually expecting to be received under a POST variable with the name
of the form Eg: `$_POST['my_form_name']`

    /**
     * @Rest(payloadMapping="form")
     */

### statusCodeParam

Parameter used to override status code response.

    /**
     * @Route("/")
     * @Template()
     * @Rest(statusCodeParam="status_code")
     */
    public function indexAction()
    {
        ...

        return array(
            'status_code' => '403'
        );
    }

### versionRequired

Force to send version in Accept header if true

    /**
     * @Rest(versionRequired=false)
     */

### defaultVersion

Define the default version of the api, false as default for no versioning

### interceptRedirects

Intercept 3xx redirects and responds with flash messages

### processForms

Process forms errors

### defaultFormParam

Process params with name form as principal Form for validation

### Configuration

You can set serialize null property on bundle configuration.

```yaml
wobble_code_rest:
    serialize_null: false
```

## License

Copyright (c) 2016 Luis Hdez

Released under MIT LICENSE, more info at LICENSE-MIT file.
