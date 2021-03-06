<?php

namespace ACSEO\Bundle\BaseRestBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Configuration\Route;

abstract class AbstractRestController extends FOSRestController implements ClassResourceInterface
{
    public function optionsAction()
    {
        $response = new Response();
        $response->headers->set('Allow', 'OPTIONS, GET, PATCH, POST, DELETE, PUT');

        return $response;
    }

    public function getAction($id)
    {
        if (!$this->isShowAvailable()) {
            throw new NotFoundHttpException();
        }

        $entity = $this->getEntity($id);

        return $this->handleView($this->view(
           array('entity' => $entity),
           Codes::HTTP_OK
       ));
    }

    public function deleteAction($id)
    {
        if (!$this->isDeleteAvailable()) {
            throw new NotFoundHttpException();
        }

        $entity = $this->getEntity($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $view = $this->view(
            $entity,
            Codes::HTTP_OK
        );

        return $this->handleView($view);
    }

    public function cgetAction(\FOS\RestBundle\Request\ParamFetcher $paramFetcher)
    {
        if (!$this->isIndexAvailable()) {
            throw new NotFoundHttpException();
        }

        $data = $this->getDoctrine()->getManager()->getRepository($this->getEntityName())->findBy(
            $this->getQueryParams($paramFetcher),
            $this->getQuerySortForDoctrine($this->getRequest()),
            $this->getQueryLimit($this->getRequest()),
            $this->getQueryOffset($this->getRequest())
        );

        return $this->handleView($this->view(
            $data,
            Codes::HTTP_OK
        ));
    }

    public function postAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        if (!$this->isNewAvailable()) {
            throw new NotFoundHttpException();
        }

        $entity = $this->createEntityInstance();
        $form = $this->createForm($this->createEntityType(), $entity);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $view = $this->view(
                // $this->generateUrl(
                //     $this->getRoute("show"),
                //     array(
                //         'id' => $entity->getId()
                //     )
                // ),
                $entity,
                Codes::HTTP_CREATED
            );

            return $this->handleView($view);
        }

        return array(
            'form' => $form,
        );
    }

    public function putAction(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        if (!$this->isEditAvailable()) {
            throw new NotFoundHttpException();
        }

        $entity = $entity = $this->getEntity($id);
        $form = $this->createForm($this->createEntityType(), $entity, array('method' => 'PUT'));
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $view = $this->view(
                $entity,
                Codes::HTTP_OK
            );

            return $this->handleView($view);

            //return new \Symfony\Component\HttpFoundation\Response(null, Codes::HTTP_NO_CONTENT);
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Add parameter manually to @Rest\QueryParam
     */
    protected function addQueryParam(\FOS\RestBundle\Request\ParamFetcher $paramFetcher, $paramArray)
    {
        $param = new Rest\QueryParam();

        foreach ($paramArray as $key => $value) {
            if (!property_exists($param, $key)) {
                throw new UnprocessableEntityHttpException();
            }

            $param->$key = $value;
        }

        $paramFetcher->addParam($param);

        return $paramFetcher;
    }

    /**
     * Fetch parameters transmited by @Rest\QueryParam and not begining by "_"
     * Can be used to created a SQL query and search data.
     */
    protected function getQueryParams($paramFetcher)
    {
        $params = array();
        foreach ($paramFetcher->all() as $criterionName => $criterionValue) {
            if (null != $criterionValue && substr($criterionName, 0, 1) != '_') {
                $params[$criterionName] = $criterionValue;
            }
        }

        return $params;
    }

    /**
     * Search the specific parameter "_per_page" in the request.
     *
     * @return the number of item per page to display
     */
    protected function getQueryLimit($request)
    {
        $data = $request->query->all();
        if (isset($data['_per_page']) &&
            $data['_per_page'] <= $this->container->getParameter('acseo_base_rest.listing_max_per_page')
            ) {
            return $data['_per_page'];
        }

        return $this->container->getParameter('acseo_base_rest.listing_max_per_page');
    }

    protected function getQueryMaxPerPage($request)
    {
        return $this->getQueryLimit($request);
    }

    /**
     * Search the specific parameter "_page" in the request.
     *
     * @return the current page number
     */
    protected function getQueryCurrentPage($request)
    {
        $data = $request->query->all();
        if (isset($data['_page'])) {
            return ($data['_page']);
        }

        return 1;
    }

    protected function getQueryOffset($request)
    {
        $data = $request->query->all();
        if (isset($data['_page'])) {
            return $this->getQueryCurrentPage($request) * $this->getQueryLimit($request);
        }

        return 0;
    }

    protected function getQuerySort($request)
    {
        $sort = array();
        $data = $request->query->all();
        if (isset($data['_sort'])) {
            $key = $data['_sort'];
            $value = 'ASC';
            if (isset($data['_sort_order']) && (strtoupper($data['_sort_order']) == 'ASC' || strtoupper($data['_sort_order']) == 'DESC')) {
                $value = strtoupper($data['_sort_order']);
            }

            $sort = array('_sort' => $key, '_sort_order' => $value);
        }

        return $sort;
    }

    protected function getQuerySortForDoctrine($request)
    {
        $data = $this->getQuerySort($request);
        if (sizeof($data) != 0) {
            return array($data['_sort'] => $data['_sort_order']);
        }

        return;
    }

    /**
     * Get entity instance.
     *
     * @var int Id of the entity
     *
     * @return Contact
     */
    protected function getEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($this->getEntityName())->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('The %s \'%s\' was not found.', $this->getEntityHumanName(), $id));
        }

        return $entity;
    }

    protected function isIndexAvailable()
    {
        return true;
    }

    protected function isShowAvailable()
    {
        return true;
    }

    protected function isNewAvailable()
    {
        return false;
    }

    protected function isEditAvailable()
    {
        return false;
    }

    protected function isDeleteAvailable()
    {
        return true;
    }

    private function getRoute($actionCode)
    {
        switch ($actionCode) {
            case 'index':
                $entityTab = explode(':', $this->getEntityName());

                return sprintf('get_%ss', strtolower($entityTab[1]));
                break;
            case 'show':
                $entityTab = explode(':', $this->getEntityName());

                return sprintf('get_%s', strtolower($entityTab[1]));
                break;
            default:
                throw new \Exception(sprintf('%s is not a recognized action code', $actionCode));
                break;
        }
    }

    protected function handleView(View $view) {
        $serializeGroups = $this->get('request')->get('serialize', null);

        if (is_null($serializeGroups) === false && strlen($serializeGroups) > 0) {
           $view->setSerializationContext(SerializationContext::create()->setGroups(array($serializeGroups)));
        }

       return parent::handleView($view);
   }

    abstract protected function getEntityName();
    abstract protected function getEntityHumanName();
    //abstract protected function getEntityInSentenceName();
    //abstract protected function getEntityPluralHumanName();

    abstract protected function createEntityInstance();
    abstract protected function createEntityType();
}
