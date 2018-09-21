<?php

namespace Carbon\ApiBundle\Entity\Production;

interface BaseRequestProjectInterface
{
    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId();

    /**
     * Sets the value of id.
     *
     * @param integer $id the id
     *
     * @return self
     */
    public function setId($id);

    /**
     * Gets the value of requestId.
     *
     * @return integer
     */
    public function getRequestId();

    /**
     * Sets the value of requestId.
     *
     * @param integer $requestId the request id
     *
     * @return self
     */
    public function setRequestId($requestId);

    /**
     * Gets the value of request.
     *
     * @return mixed
     */
    public function getRequest();

    /**
     * Sets the value of request.
     *
     * @param mixed $request the request
     *
     * @return self
     */
    public function setRequest($request);

    /**
     * Gets the value of projectId.
     *
     * @return integer
     */
    public function getProjectId();

    /**
     * Sets the value of projectId.
     *
     * @param integer $projectId the project id
     *
     * @return self
     */
    public function setProjectId($projectId);

    /**
     * Gets the value of project.
     *
     * @return mixed
     */
    public function getProject();

    /**
     * Sets the value of project.
     *
     * @param mixed $project the project
     *
     * @return self
     */
    public function setProject($project);
}
