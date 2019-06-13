<?php

namespace SoliDry\Blocks;

use SoliDry\ApiGenerator;
use SoliDry\Controllers\BaseCommand;
use SoliDry\Helpers\Classes;
use SoliDry\Types\ApiInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DocumentationInterface;
use SoliDry\Types\PhpInterface;

/**
 * Class Documentation
 *
 * @package SoliDry\Blocks
 *
 * @property BaseCommand generator
 */
abstract class Documentation
{

    use ContentManager;

    protected $generator;
    protected $sourceCode = '';
    protected $className;

    /**
     * Controllers constructor.
     *
     * @param ApiGenerator $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    protected function setDefaultDocs()
    {
        $this->setComment(DefaultInterface::METHOD_START);

        $this->openComment();

        // generate basic info
        $this->setStarredComment(DocumentationInterface::OA_INFO . PhpInterface::OPEN_PARENTHESES);

        $this->setInfoParams();

        // generate contact info
        $this->setContactInfo();

        // generate license info
        $this->setLicenseInfo();

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();

        $this->setComment(DefaultInterface::METHOD_END);
    }

    /**
     *  Sets info params - title, version, description
     */
    private function setInfoParams(): void
    {
        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_TITLE]) === false) {
            $this->setStarredComment('title="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_TITLE] . '",',
                1, 1);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_VERSION]) === false) {
            $this->setStarredComment('version="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_VERSION] . '",',
                1, 1);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_DESCRIPTION]) === false) {
            $this->setStarredComment('description="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_DESCRIPTION] . '",',
                1, 1);
        }
    }

    /**
     *  Sets license info - name, url
     */
    private function setLicenseInfo(): void
    {
        $this->setStarredComment(DocumentationInterface::OA_LICENSE . PhpInterface::OPEN_PARENTHESES,
            1, 1);

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_NAME]) === false) {
            $this->setStarredComment('name="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_NAME] . '",',
                1, 2);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_URL]) === false) {
            $this->setStarredComment('url="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_LICENSE][ApiInterface::API_URL] . '",',
                1, 2);
        }

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES . PhpInterface::COMMA, 1, 1);
    }

    /**
     *  Sets contact info - email, name, url
     */
    private function setContactInfo(): void
    {
        $this->setStarredComment(DocumentationInterface::OA_CONTACT . PhpInterface::OPEN_PARENTHESES,
            1, 1);

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_EMAIL]) === false) {
            $this->setStarredComment('email="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_EMAIL] . '",',
                1, 2);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_NAME]) === false) {
            $this->setStarredComment('name="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_NAME] . '",',
                1, 2);
        }

        if (empty($this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_URL]) === false) {
            $this->setStarredComment('url="' . $this->generator->data[ApiInterface::API_INFO][ApiInterface::API_CONTACT][ApiInterface::API_URL] . '",',
                1, 2);
        }

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES . PhpInterface::COMMA, 1, 1);
    }

    /**
     *  Sets doc comments for every controller
     */
    protected function setControllersDocs(): void
    {
        $this->setComment(DefaultInterface::METHOD_START);

        $this->setIndex();

        $this->setView();

        $this->setCreate();

        $this->setUpdate();

        $this->setDelete();

        $this->setComment(DefaultInterface::METHOD_END);
    }

    private function setIndex(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_GET . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . '",', 1, 1);

        $this->setStarredComment('summary="Get ' . $this->generator->objectName . 's ",', 1, 1);

        $this->setStarredComment('tags={"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        // define params
        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"include"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"page"',
            'required' => 'false',
        ], 'integer');

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"limit"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"sort"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"data"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"filter"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"order_by"',
            'required' => 'false',
        ]);

        $this->setResponse([
            'response'    => '200',
            'description' => '""',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    private function setView(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_GET . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . PhpInterface::SLASH . '{id}",', 1, 1);

        $this->setStarredComment('summary="Get ' . $this->generator->objectName . '",', 1, 1);

        $this->setStarredComment('tags={"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"include"',
            'required' => 'false',
        ]);

        $this->setParameter([
            'in'       => '"query"',
            'name'     => '"data"',
            'required' => 'false',
        ]);

        $this->setResponse([
            'response'    => '200',
            'description' => '""',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    private function setCreate(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_POST . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . '",', 1, 1);

        $this->setStarredComment('summary="Create ' . $this->generator->objectName . '",', 1, 1);

        $this->setStarredComment('tags={"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setResponse([
            'response'    => '200',
            'description' => '""',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    private function setUpdate(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_PATCH . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . PhpInterface::SLASH . '{id}",', 1, 1);

        $this->setStarredComment('summary="Update ' . $this->generator->objectName . '",', 1, 1);

        $this->setStarredComment('tags={"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setResponse([
            'response'    => '200',
            'description' => '""',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    private function setDelete(): void
    {
        $this->openComment();

        $this->setStarredComment(DocumentationInterface::OA_DELETE . PhpInterface::OPEN_PARENTHESES);

        $this->setStarredComment('path="' . PhpInterface::SLASH . $this->generator->version . PhpInterface::SLASH
            . strtolower($this->generator->objectName) . PhpInterface::SLASH . '{id}",', 1, 1);

        $this->setStarredComment('summary="Delete ' . $this->generator->objectName . '",', 1, 1);

        $this->setStarredComment('tags={"' . $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
            . '"},', 1, 1);

        $this->setResponse([
            'response'    => '200',
            'description' => '""',
        ]);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES);

        $this->closeComment();
        $this->setNewLines();
    }

    /**
     * Sets any params of methods
     *
     * @param array $paramValues
     * @param string $schemaType
     */
    private function setParameter(array $paramValues, string $schemaType = 'string'): void
    {
        $this->setStarredComment(DocumentationInterface::OA_PARAMETER . PhpInterface::OPEN_PARENTHESES, 1, 1);
        foreach ($paramValues as $key => $val) {
            $this->setStarredComment($key . '=' . $val . ',', 1, 2);
        }

        $this->setSchema($schemaType);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES . PhpInterface::COMMA, 1, 1);
    }

    /**
     * Sets the parameter schema
     *
     * @param string $schemaType
     */
    private function setSchema(string $schemaType): void
    {
        $this->setStarredComment(DocumentationInterface::OA_SCHEMA . PhpInterface::OPEN_PARENTHESES, 1, 2);

        $this->setStarredComment('type="' . $schemaType . '",', 1, 3);

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES . PhpInterface::COMMA, 1, 2);
    }

    /**
     * Sets any response of method
     *
     * @param array $paramValues
     */
    private function setResponse(array $paramValues): void
    {
        $this->setStarredComment(DocumentationInterface::OA_RESPONSE . PhpInterface::OPEN_PARENTHESES, 1, 1);
        foreach ($paramValues as $key => $val) {
            $this->setStarredComment($key . '=' . $val . ',', 1, 2);
        }

        $this->setStarredComment(PhpInterface::CLOSE_PARENTHESES . PhpInterface::COMMA, 1, 1);
    }
}