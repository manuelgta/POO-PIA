<?php
    function insertLog ($conn, $tableName, $recordId, $userId, $newValues) {
        $tableName = $tableName ?? NULL;
        $recordId = $recordId ?? NULL;
        $userId = $userId ?? NULL;
        $newValues = $newValues ?? NULL;

        if (is_null($tableName) || is_null($recordId)) return false;

        $stmt = $conn->prepare("INSERT INTO logs (tableName, recordId, actionType, userId, newValues)
        VALUES (?, ?, 'INSERT', ?, ?)");
        $stmt->bind_param("siis", $tableName, $recordId, $userId, $newValues);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    function updateLog ($conn, $tableName, $recordId, $userId, $oldValues, $newValues) {
        $tableName = $tableName ?? NULL;
        $recordId = $recordId ?? NULL;
        $userId = $userId ?? NULL;
        $oldValues = $oldValues ?? NULL;
        $newValues = $newValues ?? NULL;

        if (in_array(NULL, [$tableName, $recordId, $userId, $oldValues, $newValues], true)) return false;

        $stmt = $conn->prepare("INSERT INTO logs (tableName, recordId, actionType, userId, oldValues, newValues)
        VALUES (?, ?, 'UPDATE', ?, ?, ?)");
        $stmt->bind_param("siiss", $tableName, $recordId, $userId, $oldValues, $newValues);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    function deleteLog ($conn, $tableName, $recordId, $userId, $oldValues) {
        $tableName = $tableName ?? NULL;
        $recordId = $recordId ?? NULL;
        $userId = $userId ?? NULL;
        $oldValues = $oldValues ?? NULL;

        if (is_null($tableName) || is_null($recordId) || is_null($userId)) return false;
        
        $stmt = $conn->prepare("INSERT INTO logs (tableName, recordId, actionType, userId, oldValues)
        VALUES (?, ?, 'DELETE', ?, ?)");
        $stmt->bind_param("siis", $tableName, $recordId, $userId, $oldValues);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    function restoreLog ($conn, $tableName, $recordId, $userId, $newValues) {
        $tableName = $tableName ?? NULL;
        $recordId = $recordId ?? NULL;
        $userId = $userId ?? NULL;
        $newValues = $newValues ?? NULL;

        if (is_null($tableName) || is_null($recordId) || is_null($userId) || is_null($newValues)) return false;
        
        $stmt = $conn->prepare("INSERT INTO logs (tableName, recordId, actionType, userId, newValues)
        VALUES (?, ?, 'RESTORE', ?, ?)");
        $stmt->bind_param("siis", $tableName, $recordId, $userId, $newValues);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }
?>
