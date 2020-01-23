<?php


namespace Cactus\Controller\SearchSchool;


use Cactus\Database\CsvDatabase;
use Cactus\Routing\Exception\RouteNotFoundException;
use Cactus\Template\Controller\ITemplateController;
use Cactus\Template\Render\RenderContext;

class SelectSchoolController implements ITemplateController
{
    private const SCHOOL_ID = 0;
    private const SCHOOL_NAME = 1;
    private const DEPARTMENT_CODE = 10;
    private const REGION_CODE = 12;

    private const SCHOOL_PER_PAGES = 8;

    /**
     * @param RenderContext $context
     * @return string
     * @throws RouteNotFoundException
     */
    public function get_schools(RenderContext $context): string
    {
        $regionCode = $context->param("route.region");
        $departmentCode = $context->param("route.department");
        $schoolType = $context->param("route.school_type");

        $schoolDatabase = new CsvDatabase($schoolType, ';');
        $schoolDatabase->open();
        $schools = $schoolDatabase->get(function ($entry) use ($departmentCode, $regionCode) {
            return $entry[self::REGION_CODE] === $regionCode && $entry[self::DEPARTMENT_CODE] === $departmentCode;
        });
        $schoolDatabase->close();

        usort($schools, function ($a, $b) {
            return $a[self::SCHOOL_NAME] <=> $b[self::SCHOOL_NAME];
        });

        $output = "";

        $page = intval($context->param("route.page"));
        $offset = $page * self::SCHOOL_PER_PAGES;
        $schoolCount = count($schools);
        for ($i = 0; $i < self::SCHOOL_PER_PAGES && (($offset + $i) < $schoolCount); $i++) {
            $school = $schools[$offset + $i];

            $url = $context->buildUrl("GET", "sign-up", [
                "school_id" => $school[self::SCHOOL_ID]
            ]);
            $schoolName = $school[self::SCHOOL_NAME];
            $output .= "<li class=\"grid-list-item\"><a class=\"button\" href=\"$url\">$schoolName</a></li>";
        }

        return $output;
    }
}