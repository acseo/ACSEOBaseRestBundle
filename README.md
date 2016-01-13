# ACSEOBaseRestBundle
Prodive a base REST Bundle that can be easily extended

## Purpose

This Bundle exposes a Base Rest Bundle that can be easily extended to provide access to your entities.

## Installation

1) Add the bundle to you composer.json file :
```
composer require 'acseo/base-rest-bundle:dev-master'
```

2) Enable the Bundle
```php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
        //...
        new ACSEO\Bundle\BaseRestBundle\ACSEOBaseRestBundle(),
        //...
```
That's it.

## Usage

In your controllers, get your CRUD ready to use.
Feel free to use queryParams, serialization groups or show/edit/.. rights really easily.

Example:
```php
// Application/Bundle/Controller/MyController.php

namespace Application\Bundle\Controller;

use ACSEO\Bundle\BaseRestBundle\Controller\AbstractRestController as ACSEOBaseRestController;
// ...

class MyController extends ACSEOBaseRestController
{
    // Your CRUD ready to use :
    /**
     * Get Action.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Lists Entities",
     *  statusCodes = {
     *    200 = "Returned when successful",
     *    404 = "Returned when the entity is not found"
     *  }
     * )
     *
     * @Rest\View()
     * @Rest\Get("/my_entities/{id}")
     */
    public function getAction($id)
    {
        return parent::getAction($id);
    }

    /**
     * Post action.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Save entity",
     *   input="Application\Bundle\Form\MyEntityType",
     *   output="Application\Bundle\Entity\MyEntity",
     *   statusCodes = {
     *     201 = "Entity created",
     *     400 = "Data is invalid"
     *   },
     * )
     *
     * @var Request
     *
     * @Rest\View(
     *  statusCode = Codes::HTTP_BAD_REQUEST,
     *  templateVar = "form"
     * )
     *
     * @Rest\Post("/my_entities")
     *
     */
    public function postAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        return parent::postAction($request);
    }

    /**
     * ...
     */
    public function cgetAction(\FOS\RestBundle\Request\ParamFetcher $paramFetcher)
    {
        ...
    }

    /**
     * ...
     */
    public function deleteAction($id)
    {
        ...
    }

    /**
     * ...
     */
    public function putAction(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        ...
    }

    // Your custom action
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="route description",
     *  statusCodes = {
     *    200 = "Returned when successful",
     *    404 = "Returned when the entity is not found"
     *  }
     * )
     *
     * @Rest\View()
     * @Rest\Get("/entity/{id}/custom")
     * @Rest\QueryParam(name="param", key="param", default=null, nullable=false, description="my description")
     */
    public function getCustomAction($id, $paramFetcher)
    {
        $entity = $this->getEntity($id);
        $queryParams = $this->getQueryParams($paramFetcher);

        $param = array_key_exists('param', $queryParams) ? $queryParams['param'] : false;

        if ($param)
        {
            // Do something
        }

        $view = $this->view(
            $entity,
            Codes::HTTP_CREATED
        );

        return $this->handleView($view);
    }

    // Config
    protected function getEntityName()
    {
        return 'ApplicationMyBundle:MyEntity';
    }

    protected function getEntityHumanName()
    {
        return 'MyEntity';
    }

    protected function createEntityInstance()
    {
        return new MyEntity();
    }

    protected function createEntityType()
    {
        return new UserType($this->getDoctrine()->getEntityManager());
    }

    protected function isNewAvailable()
    {
        return true;
    }

    protected function isEditAvailable()
    {
        return true;
    }
}
```
