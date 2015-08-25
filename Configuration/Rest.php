<?php

/*
 * This file is part of the WobbleCodeRestBundle package.
 *
 * (c) WobbleCode <http://www.wobblecode.com/>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace WobbleCode\RestBundle\Configuration;

/**
 * @Annotation
 */
class Rest extends ConfigurationAnnotation
{
    /**
     * List of default objects to serialize
     *
     * @var array
     */
    private $output = array('entity', 'entities', 'metadata');

    /**
     * List of default objects to serialize, if empty it won't define a group
     *
     * @var array
     */
    private $serializeGroups = [];

    /**
     * List of accepted headers that enables REST
     *
     * @var array
     */
    private $acceptedContent = array('application/json');

    /**
     * Define if the payload is assigned to a form based in its name
     *
     * @var string
     */
    private $payloadMapping = false;

    /**
     * Parameter used to override status code response
     *
     * @var string
     */
    private $statusCodeParam = 'status_code';

    /**
     * Force to send version in Accept header
     *
     * @var boolean
     */
    private $versionRequired = false;

    /**
     * Define version of the api, false as default for no versioning
     *
     * @var string
     * @see http://php.net/manual/en/function.version-compare.php
     */
    private $defaultVersion = false;

    /**
     * Intercept 3xx redirects and responds with flash messages
     *
     * @var boolean
     */
    private $interceptRedirects = true;

    /**
     * Process forms errors
     *
     * @var boolean
     */
    private $processForms = true;

    /**
     * Process params with name form as principal Form for validation
     *
     * @var string
     */
    private $defaultFormParam = 'form';

    /**
     * Set output
     *
     * @param array
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get output
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set output
     *
     * @param array
     */
    public function setSerializeGroups($serializeGroups)
    {
        $this->serializeGroups = $serializeGroups;
    }

    /**
     * Get output
     *
     * @return array
     */
    public function getSerializeGroups()
    {
        return $this->serializeGroups;
    }

    /**
     * Set triggers
     *
     * @param array
     */
    public function setAcceptedContent($acceptedContent)
    {
        $this->acceptedContent = $acceptedContent;
    }

    /**
     * Get acceptedContent
     *
     * @return array
     */
    public function getAcceptedContent()
    {
        return $this->acceptedContent;
    }

    /**
     * Set PayloadMapping
     *
     * @param array
     */
    public function setPayloadMapping($payloadMapping)
    {
        $this->payloadMapping = $payloadMapping;
    }

    /**
     * Get payloadMapping
     *
     * @return array
     */
    public function getPayloadMapping()
    {
        return $this->payloadMapping;
    }

    /**
     * Set statusCodeParam
     *
     * @param string
     */
    public function setStatusCodeParam($statusCodeParam)
    {
        $this->statusCodeParam = $statusCodeParam;
    }

    /**
     * Get statusCodeParam
     *
     * @return string
     */
    public function getStatusCodeParam()
    {
        return $this->statusCodeParam;
    }

    /**
     * Set versionRequired
     *
     * @param boolean
     */
    public function setVersionRequired($versionRequired)
    {
        $this->versionRequired = $versionRequired;
    }

    /**
     * Get versionRequired
     *
     * @return boolean
     */
    public function getVersionRequired()
    {
        return $this->versionRequired;
    }

    /**
     * Set defaultVersion
     *
     * @param string
     */
    public function setDefaultVersion($defaultVersion)
    {
        $this->defaultVersion = $defaultVersion;
    }

    /**
     * Get defaultVersion
     *
     * @return string
     */
    public function getDefaultVersion()
    {
        return $this->defaultVersion;
    }

    /**
     * Set interceptRedirects
     *
     * @param boolean
     */
    public function setInterceptRedirects(\boolean $interceptRedirects)
    {
        $this->interceptRedirects = $interceptRedirects;
    }

    /**
     * Get interceptRedirects
     *
     * @return boolean
     */
    public function getInterceptRedirects()
    {
        return $this->interceptRedirects;
    }

    /**
     * Set processForms
     *
     * @param boolean
     */
    public function setProcessForms(\boolean $processForms)
    {
        $this->processForms = $processForms;
    }

    /**
     * Get processForms
     *
     * @return boolean
     */
    public function getProcessForms()
    {
        return $this->processForms;
    }

    /**
     * Set defaultFormParam
     *
     * @param string
     */
    public function setDefaultFormParam($defaultFormParam)
    {
        $this->defaultFormParam = $defaultFormParam;
    }

    /**
     * Get defaultFormParam
     *
     * @return boolean
     */
    public function getDefaultFormParam()
    {
        return $this->defaultFormParam;
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'rest';
    }

    /**
     * Only one template directive is allowed
     *
     * @return Boolean
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return false;
    }
}
