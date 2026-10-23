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

if (!function_exists('nu_get_table_columns_helper')) {
    function nu_get_table_columns_helper($table) {
        static $cache = [];
        if (empty($table)) return [];
        if (isset($cache[$table])) return $cache[$table];
        try {
            $cols = [];
            if (class_exists('NuDatabase')) {
                $db = NuDatabase::getInstance();
                $pdo = $db->getPdo();
                $driver = strtolower((string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
                if ($driver === 'sqlite') {
                    $rows = $pdo->query("PRAGMA table_info(`{$table}`)")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) $cols[$row['name']] = true;
                } else {
                    $rows = $pdo->query("DESCRIBE `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) $cols[$row['Field']] = true;
                }
            }
            $cache[$table] = $cols;
            return $cols;
        } catch (Throwable $e) {
            $cache[$table] = [];
            return [];
        }
    }
}

if (!function_exists('nu_decode_form_layout')) {
    function nu_decode_form_layout($form) {
        $layoutStr = $form['form_layout'] ?? $form['layout'] ?? '';
        $layout = [];
        if (!empty($layoutStr)) {
            $layout = is_array($layoutStr) ? $layoutStr : (json_decode((string)$layoutStr, true) ?: []);
        }

        if (empty($layout) && !empty($form['form_table'] ?? $form['table'] ?? '')) {
            $table = (string)($form['form_table'] ?? $form['table']);
            $cols = nu_get_table_columns_helper($table);
            $fields = [];
            foreach (array_keys($cols) as $col) {
                if (in_array($col, ['id', 'created_at', 'updated_at'], true)) continue;
                $label = ucwords(str_replace('_', ' ', $col));
                $type  = 'text';
                if (strpos($col, 'date') !== false) $type = 'date';
                if (strpos($col, 'time') !== false) $type = 'datetime';
                if (in_array($col, ['notes', 'details', 'description', 'pest_findings', 'recommendations', 'address'], true) || strpos($col, 'desc') !== false) $type = 'textarea';
                if (strpos($col, 'cost') !== false || strpos($col, 'amount') !== false || strpos($col, 'qty') !== false || strpos($col, 'quantity') !== false || strpos($col, 'price') !== false || strpos($col, 'vol') !== false || strpos($col, 'weight') !== false) $type = 'number';

                $fields[] = [
                    'name' => $col,
                    'label' => $label,
                    'type' => $type,
                    'col' => 6
                ];
            }
            $layout = $fields;
        }

        return $layout;
    }
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
