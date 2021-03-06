<?php
namespace App\Controller\Api\Admin;

use App\Controller\Api\AbstractApiCrudController;
use App\Entity;
use App\Exception;
use App\Http\Response;
use App\Http\ServerRequest;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAdminApiCrudController extends AbstractApiCrudController
{
    public function listAction(ServerRequest $request, Response $response): ResponseInterface
    {
        $query = $this->em->createQuery('SELECT e FROM ' . $this->entityClass . ' e');

        return $this->listPaginatedFromQuery($request, $response, $query);
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function createAction(ServerRequest $request, Response $response): ResponseInterface
    {
        $row = $this->_createRecord($request->getParsedBody());

        $return = $this->viewRecord($row, $request);
        return $response->withJson($return);
    }

    /**
     * @param array $data
     *
     * @return object
     */
    protected function _createRecord($data): object
    {
        return $this->editRecord($data, null);
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @param mixed $id
     *
     * @return ResponseInterface
     */
    public function getAction(ServerRequest $request, Response $response, $id): ResponseInterface
    {
        $record = $this->_getRecord($id);

        if (null === $record) {
            return $response->withStatus(404)
                ->withJson(new Entity\Api\Error(404, __('Record not found!')));
        }

        $return = $this->viewRecord($record, $request);
        return $response->withJson($return);
    }

    /**
     * @param mixed $id
     *
     * @return object|null
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    protected function _getRecord($id)
    {
        return $this->em->find($this->entityClass, $id);
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @param mixed $id
     *
     * @return ResponseInterface
     */
    public function editAction(ServerRequest $request, Response $response, $id): ResponseInterface
    {
        $record = $this->_getRecord($id);

        if (null === $record) {
            return $response->withStatus(404)
                ->withJson(new Entity\Api\Error(404, __('Record not found!')));
        }

        $this->editRecord($request->getParsedBody(), $record);

        return $response->withJson(new Entity\Api\Status(true, __('Changes saved successfully.')));
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @param mixed $id
     *
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequest $request, Response $response, $id): ResponseInterface
    {
        $record = $this->_getRecord($id);

        if (null === $record) {
            return $response->withStatus(404)
                ->withJson(new Entity\Api\Error(404, __('Record not found!')));
        }

        $this->deleteRecord($record);

        return $response->withJson(new Entity\Api\Status(true, __('Record deleted successfully.')));
    }
}
