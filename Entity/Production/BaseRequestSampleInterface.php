<?php

namespace Carbon\ApiBundle\Entity\Production;

interface BaseRequestSampleInterface
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
     * Gets the value of sampleId.
     *
     * @return integer
     */
    public function getSampleId();

    /**
     * Sets the value of sampleId.
     *
     * @param integer $sampleId the sample id
     *
     * @return self
     */
    public function setSampleId($sampleId);

    /**
     * Gets the value of sample.
     *
     * @return mixed
     */
    public function getSample();

    /**
     * Sets the value of sample.
     *
     * @param mixed $sample the sample
     *
     * @return self
     */
    public function setSample($sample);
}
