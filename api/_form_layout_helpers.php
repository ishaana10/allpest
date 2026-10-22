<?php

function nu_flatten_layout_fields($layout) {
    if (is_string($layout)) {
        $decoded = json_decode($layout, true);
        $layout = is_array($decoded) ? $decoded : [];
    }

    $out = [];

    $walk = function($items) use (&$walk, &$out) {
        if (!is_array($items)) return;

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $type = strtolower(trim((string)($item['type'] ?? '')));

            // ── Tab container ──────────────────────────────────────────────
            if ($type === 'tab') {
                foreach (($item['tabs'] ?? []) as $tab) {
                    if (!is_array($tab)) continue;
                    foreach (($tab['rows'] ?? []) as $row) {
                        $rowType = strtolower(trim((string)($row['type'] ?? 'row')));
                        if ($rowType === 'group') {
                            $walk($row['rows'] ?? []);
                        } else {
                            $walk($row['fields'] ?? []);
                            $walk($row['children'] ?? []);
                        }
                    }
                }
                continue;
            }

            // ── Section or Group ───────────────────────────────────────────
            if ($type === 'section' || $type === 'group') {
                $walk($item['children'] ?? []);
                $walk($item['fields'] ?? []);
                foreach (($item['rows'] ?? []) as $row) {
                    $walk($row['fields'] ?? []);
                    $walk($row['children'] ?? []);
                }
                continue;
            }

            // ── Plain row ──────────────────────────────────────────────────
            if ($type === 'row') {
                $walk($item['fields'] ?? []);
                $walk($item['children'] ?? []);
                continue;
            }

            // ── Subform — skip, handled separately ────────────────────────
            if ($type === 'subform') {
                continue;
            }

            // ── Skip layout-only / non-data types ─────────────────────────
            if (in_array($type, ['html', 'content', 'button', 'fieldset', 'heading', 'divider'], true)) {
                continue;
            }

            // ── Leaf field — must have a name ──────────────────────────────
            if (!empty($item['name']) || !empty($item['fieldname'])) {
                $out[] = $item;
            }
        }
    };

    $walk($layout);
    return $out;
}

function nu_flatten_layout($layout) {
    return nu_flatten_layout_fields($layout);
}

function nu_flatten_subforms_from_layout($layout) {
    if (is_string($layout)) {
        $decoded = json_decode($layout, true);
        $layout = is_array($decoded) ? $decoded : [];
    }

    $out = [];

    $walk = function($items) use (&$walk, &$out) {
        if (!is_array($items)) return;

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $type = strtolower(trim((string)($item['type'] ?? '')));

            if ($type === 'subform') {
                $out[] = $item;
                continue;
            }

            // ── Tab container ──────────────────────────────────────────────
            if ($type === 'tab') {
                foreach (($item['tabs'] ?? []) as $tab) {
                    if (!is_array($tab)) continue;
                    foreach (($tab['rows'] ?? []) as $row) {
                        $rowType = strtolower(trim((string)($row['type'] ?? 'row')));
                        if ($rowType === 'group') {
                            $walk($row['rows'] ?? []);
                        } else {
                            $walk($row['fields'] ?? []);
                            $walk($row['children'] ?? []);
                        }
                    }
                }
                continue;
            }

            // ── Section or Group ───────────────────────────────────────────
            if ($type === 'section' || $type === 'group') {
                $walk($item['children'] ?? []);
                $walk($item['fields'] ?? []);
                foreach (($item['rows'] ?? []) as $row) {
                    $walk($row['fields'] ?? []);
                    $walk($row['children'] ?? []);
                }
                continue;
            }

            // ── Plain row ──────────────────────────────────────────────────
            if ($type === 'row') {
                $walk($item['fields'] ?? []);
                $walk($item['children'] ?? []);
                continue;
            }
        }
    };

    $walk($layout);
    return $out;
}
