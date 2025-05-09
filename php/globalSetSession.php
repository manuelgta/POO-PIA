<?php
    session_start();

    function failed() {
        echo json_encode(["success" => false, "message" => "No se recibió el valor"]);
        exit;
    }

    function success($data = NULL) {
        echo json_encode(["success" => true] + ($data ? ["session" => $data] : []));
        exit;
    }

    function unsetDashboardStuff() {
        unset($_SESSION['dashboard']['regId']);
        unset($_SESSION['dashboard']['set']);
        unset($_SESSION['dashboard']['delete']);
    }

    // Configuracion por pagina con lógica especial
    $sessionConfig = [
        'dashboard' => [
            'beforeSet' => function ($criteria) {
                if (is_array($criteria) && in_array('table', $criteria)) {
                    unsetDashboardStuff();
                } elseif ($criteria === 'table') {
                    unsetDashboardStuff();
                }
            },
            'customSet' => function ($criteria, $data) {
                if (isset($_POST['isArray']) && $_POST['isArray'] != 'false') {
                    $_SESSION['dashboard'][$_POST['isArray']][$criteria] = $data;
                } else {
                    $_SESSION['dashboard'][$criteria] = $data;
                }
            }
        ],
        'report' => [
            'customHandler' => function () {
                $criteria = $_POST['criteria'];
                $data = $_POST['data'] ?? null;

                if (is_array($criteria)) {
                    if (count($criteria) > 2) {
                        foreach ($criteria as $key => $value) {
                            if (isset($data[$key])) {
                                $_SESSION['report'][$value] = $data[$key];
                            } else {
                                unset($_SESSION['report'][$value]);
                            }
                        }
                    } else {
                        if ($data === null) {
                            unset($_SESSION['report'][$criteria[0]][$criteria[1]]);
                        } else {
                            $_SESSION['report'][$criteria[0]][$criteria[1]] = $data;
                        }
                    }
                } else {
                    if ($criteria === 'reset') {
                        unset($_SESSION['report']);
                    } else {
                        $_SESSION['report'][$criteria] = $data;
                    }
                }

                success($data);
            }
        ],
        'structure' => [
            'customHandler' => function () {
                if (isset($_POST['table'], $_POST['id'], $_POST['old'], $_POST['criteria'])) {
                    $table = $_POST['table'];
                    $id = $_POST['id'];
                    $criteria = $_POST['criteria'];
                    $tableSLess = substr($table, 0, -1);

                    switch ($criteria) {
                        case 'edit':
                            $_SESSION['reportStructure'][$table][$id] = $_POST['old'];
                            break;
                        case 'revert':
                            unset($_SESSION['reportStructure'][$table][$id]);
                            unset($_SESSION[$tableSLess . $id]);
                            break;
                    }
                    success();
                } else {
                    failed();
                }
            }
        ]
    ];

    // Codigo general para paginas sin lógica especial
    function setSessionData($sessionKey, $config = []) {
        $criteria = $_POST['criteria'] ?? null;
        $data = $_POST['data'] ?? null;

        if (isset($config['beforeSet']) && is_callable($config['beforeSet'])) {
            $config['beforeSet']($criteria);
        }

        if (is_array($criteria)) {
            foreach ($criteria as $key => $currentCriteria) {
                $_SESSION[$sessionKey][$currentCriteria] = $data[$key] ?? null;
            }
        } else {
            if (isset($_POST['unset']) && $_POST['unset'] == 'true') {
                unset($_SESSION[$sessionKey][$criteria]);
            } else {
                if (isset($config['customSet']) && is_callable($config['customSet'])) {
                    $config['customSet']($criteria, $data);
                } else {
                    $_SESSION[$sessionKey][$criteria] = $data;
                }
            }
        }

        success($data);
    }

    // Entrada principal
    if (!isset($_POST['page'], $_POST['criteria'])) {
        failed();
    }

    $page = $_POST['page'];
    $config = $sessionConfig[$page] ?? [];

    if (isset($config['customHandler']) && is_callable($config['customHandler'])) {
        $config['customHandler'](); // Lógica completamente personalizada
    } else {
        setSessionData($page, $config); // Lógica general
    }
