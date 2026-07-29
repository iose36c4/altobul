<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GeoJsonPolygon implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('Geometría debe ser un array GeoJSON válido.');

            return;
        }

        if (! isset($value['type']) || ! in_array($value['type'], ['Polygon', 'MultiPolygon'])) {
            $fail('Geometría debe ser de tipo Polygon o MultiPolygon.');

            return;
        }

        if (! isset($value['coordinates']) || ! is_array($value['coordinates'])) {
            $fail('Coordenadas requeridas.');

            return;
        }

        $this->validateCoordinates($value['type'], $value['coordinates'], $fail);
    }

    private function validateCoordinates(string $type, array $coordinates, Closure $fail): void
    {
        if ($type === 'Polygon') {
            foreach ($coordinates as $ringIndex => $ring) {
                if (! is_array($ring) || count($ring) < 4) {
                    $fail("Anillo {$ringIndex}: debe tener al menos 4 puntos (anillo cerrado).");

                    return;
                }

                $first = $ring[0];
                $last = $ring[count($ring) - 1];
                if ($first[0] !== $last[0] || $first[1] !== $last[1]) {
                    $fail("Anillo {$ringIndex}: debe estar cerrado (primer y último punto iguales).");

                    return;
                }

                foreach ($ring as $pointIndex => $point) {
                    if (! is_array($point) || count($point) < 2 || ! is_numeric($point[0]) || ! is_numeric($point[1])) {
                        $fail("Anillo {$ringIndex}, punto {$pointIndex}: coordenadas inválidas [lng, lat].");

                        return;
                    }
                    $lng = (float) $point[0];
                    $lat = (float) $point[1];
                    if ($lng < -180 || $lng > 180 || $lat < -90 || $lat > 90) {
                        $fail("Anillo {$ringIndex}, punto {$pointIndex}: coordenadas fuera de rango (lng: -180 a 180, lat: -90 a 90).");

                        return;
                    }
                }
            }
        } elseif ($type === 'MultiPolygon') {
            foreach ($coordinates as $polygonIndex => $polygon) {
                if (! is_array($polygon)) {
                    $fail("Polígono {$polygonIndex}: estructura inválida.");

                    return;
                }
                foreach ($polygon as $ringIndex => $ring) {
                    if (! is_array($ring) || count($ring) < 4) {
                        $fail("Polígono {$polygonIndex}, anillo {$ringIndex}: debe tener al menos 4 puntos.");

                        return;
                    }

                    $first = $ring[0];
                    $last = $ring[count($ring) - 1];
                    if ($first[0] !== $last[0] || $first[1] !== $last[1]) {
                        $fail("Polígono {$polygonIndex}, anillo {$ringIndex}: debe estar cerrado.");

                        return;
                    }

                    foreach ($ring as $pointIndex => $point) {
                        if (! is_array($point) || count($point) < 2 || ! is_numeric($point[0]) || ! is_numeric($point[1])) {
                            $fail("Polígono {$polygonIndex}, anillo {$ringIndex}, punto {$pointIndex}: coordenadas inválidas.");

                            return;
                        }
                        $lng = (float) $point[0];
                        $lat = (float) $point[1];
                        if ($lng < -180 || $lng > 180 || $lat < -90 || $lat > 90) {
                            $fail("Polígono {$polygonIndex}, anillo {$ringIndex}, punto {$pointIndex}: coordenadas fuera de rango.");

                            return;
                        }
                    }
                }
            }
        }
    }
}
