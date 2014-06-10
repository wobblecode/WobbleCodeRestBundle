
# WobbleCodeRestBundle

A bundle that creates REST Apis based on your current controllers actions in
seconds using annotations.

These are the main features:

+ Works with your current controllers
+ Works with your current Symfony forms
+ Intercetps current redirects
+ Version api support
+ Api based on content negotiation
+ Integrated with JMSSerializerBundle

### Quick Example

This is the most basic example of use. Just adding the Rest annotation, the api
will be available using content negotiation. So if you request JSON, it will
return a JSON format schema, If html is requested it will fallback in @Template
so the related view will be rendered.

It will use JSMSerializer if it's possible in
the model as well.

    use WobbleCode\RestBundle\Configuration\Rest;

    ...

    /**
     * @Route("/")
     * @Template()
     * @Rest()
     */
    public function indexAction()
    {
        ...

        return array(
            'entity' => $entity
        );
    }

### License

Copyright (c) 2014 Luis Hdez

Released under MIT LICENSE, more info at LICENSE-MIT file.
