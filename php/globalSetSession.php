<?php
    session_start();

    function failed($message = "No se recibio el valor") {
        echo json_encode(["error" => true, "message" => "$message"]);
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
        'cartRemove' => [
            'customHandler' => function () {
                $data = $_POST['data'] ?? null;
                
                unset($_SESSION['cart']['items'][$data]);

                success($data);
            }
        ],
        'cartChange' => [
            'customHandler' => function () {
                $criteria = $_POST['criteria'] ?? null;
                $data = $_POST['data'] ?? null;
                
                $_SESSION['cart']['items'][$criteria] = $data;

                success($data);
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
