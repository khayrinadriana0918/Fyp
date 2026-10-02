<?php

function createNotification($pdo, $userId, $message)
{
    $stmt = $pdo->prepare("
        INSERT INTO notif (user_id,n_message) VALUES (:user_id,:message)
    ");

    return $stmt->execute([
        ':user_id' => $userId,
        ':message' => $message
    ]);
}
function getNotifs($pdo, $userId){
    $stmt =$pdo-> prepare("
    SELECT *
    FROM notif
    WHERE user_id=:user_id
    ORDER BY n_created_at DESC
    ");

    $stmt->execute([
        'user_id'=> $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getUnreadNotifCount($pdo,$userId){
    $stmt=$pdo->prepare("
    SELECT COUNT(*)
    FROM notif
    WHERE user_id=:user_id
    AND n_mark_read=0
    ");

    $stmt->execute([
        ':user_id'=> $userId
    ]);
    return $stmt->fetchColumn();
}

function markNotifRead($pdo, $notificationId, $userId){
    $stmt=$pdo->prepare("
    UPDATE notif
    SET n_mark_read=1
    WHERE n_id=:n_id
    AND user_id=:user_id
    ");

    return $stmt-> execute([
        ':n_id'=> $notificationId,
        ':user_id'=> $userId
    ]);
}