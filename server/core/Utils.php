<?php
class Utils
{
    public static function generateGuid()
    {
        return md5(uniqid(rand(), true));
    }

    public static function FilterOutEmptyData($inputData, $desiredFields)
    {
        // Filter out only the keys from $inputData that are in $desiredFields
        $filteredByKeys = array_intersect_key($inputData, array_flip($desiredFields));

        // Return only non-empty values from the filtered array
        return array_filter($filteredByKeys, function ($value) {
            return !empty($value);
        });
    }

    public static function RoundArrayValues($inputArray, $decimalPlaces = 2)
    {
        foreach ($inputArray as $key => $value) {
            if (is_numeric($value)) {
                $inputArray[$key] = round($value, $decimalPlaces);
            }
        }
        return $inputArray;
    }

    public static function addLeadingZero($number)
    {
        // Check if the number is between 1 and 9 (inclusive)
        if ($number >= 1 && $number <= 9) {
            // Add a leading zero and make sure the result is two characters long
            return str_pad($number, 2, '0', STR_PAD_LEFT);
        } else {
            // If the number is not between 1 and 9, return the original number as-is
            return $number;
        }
    }
}
