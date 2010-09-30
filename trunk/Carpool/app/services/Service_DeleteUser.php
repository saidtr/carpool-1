<?php

class Service_DeleteUser {

    public static function run($contactId) {

        $db = DatabaseHelper::getInstance();

        try {
            $db->beginTransaction();

            if (!$db->deleteRideByContact($contactId)) {
                throw new Exception("Could not delete rides for contact $contact`Id");
            }

            if (!$db->deleteContact($contactId)) {
                throw new Exception("Could not delete contact $contactId");
            }

            $db->commit();
            AuthHandler::logout();
        } catch (Exception $e) {
            Logger::logException($e);
            $db->rollBack();
            throw $e;
        }

    }

}
