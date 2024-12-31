<?php

function sortByDateProp(array $tax_reciept, string $prop): array
{
    $sortedObjects = $tax_reciept;

    usort($sortedObjects, function ($a, $b) use ($prop) {
        return   strtotime($b->$prop)   - strtotime($a->$prop) ;
    });

    return $sortedObjects;
}

function groupByYear(array $objects, string $prop): array
{
    $groupedByYear = [];

    foreach ($objects as $object) {
        $year = date('Y', strtotime($object->$prop));
        if (!isset($groupedByYear[$year])) {
            $groupedByYear[$year] = [];
        }
        $groupedByYear[$year][] = $object;
    }
    arsort($groupedByYear);

    return $groupedByYear;
}


// register the ajax action for authenticated users
