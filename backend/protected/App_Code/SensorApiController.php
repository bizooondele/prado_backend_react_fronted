<?php

class SensorApiController extends ApiResourceController
{
    public function registerReading($data)
    {
        $gate = $this->requireString($data, 'gate-nr');
        if (!isset($data['readings']) || !is_array($data['readings']) || count($data['readings']) === 0) {
            throw new ApiRequestException(400, 'At least one sensor reading is required.');
        }

        $rows = array();
        $creationTime = gmdate('Y-m-d H:i:s');
        foreach ($data['readings'] as $reading) {
            if (!is_object($reading) || !isset($reading->data) || !is_object($reading->data)) {
                throw new ApiRequestException(400, 'Each reading must be a JSON object with a data object.');
            }

            $type = $this->requireString(get_object_vars($reading), 'type');
            $idHex = $this->requireString(get_object_vars($reading->data), 'idHex');
            $this->requireString(get_object_vars($reading), 'timestamp');
            $timestamp = $reading->timestamp;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}:\d{2}:\d{2}:\d{2}$/D', $timestamp)) {
                throw new ApiRequestException(400, 'timestamp must be a valid UTC date in YYYY-MM-DD:HH:MM:SS format.');
            }
            $date = DateTimeImmutable::createFromFormat('!Y-m-d:H:i:s', $timestamp, new DateTimeZone('UTC'));
            if ($date === false || $date->format('Y-m-d:H:i:s') !== $timestamp) {
                throw new ApiRequestException(400, 'timestamp must be a valid UTC date in YYYY-MM-DD:HH:MM:SS format.');
            }

            $eid = $date->getTimestamp();
            if ($eid < -2147483648 || $eid > 2147483647) {
                throw new ApiRequestException(400, 'timestamp is outside the supported eid range.');
            }

            $aid = 'gate_' . $gate . '_' . $type . '_' . $idHex;
            $aidLength = preg_match_all('/./us', $aid);
            if ($aidLength === false || $aidLength > 255) {
                throw new ApiRequestException(400, 'The resulting aid must be valid UTF-8 and at most 255 characters.');
            }

            $json = json_encode($reading, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new ApiRequestException(400, 'The sensor reading cannot be encoded as JSON.');
            }

            $rows[] = array('aid' => $aid, 'eid' => $eid, 'data' => $json, 'creation_time' => $creationTime);
        }

        $this->saveReadings($rows);
        return array('registered' => count($rows));
    }

    protected function saveReadings($rows)
    {
        $db = Prado::getApplication()->getModule('db')->DbConnection;
        $db->setActive(true);
        $transaction = $db->beginTransaction();
        try {
            $command = $db->createCommand(
                'INSERT INTO raw_sensor_data (aid, eid, data, creation_time) VALUES (:aid, :eid, :data, :creation_time)'
            );
            foreach ($rows as $row) {
                $command->bindValue(':aid', $row['aid']);
                $command->bindValue(':eid', $row['eid'], PDO::PARAM_INT);
                $command->bindValue(':data', $row['data']);
                $command->bindValue(':creation_time', $row['creation_time']);
                $command->execute();
            }
            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollback();
            throw $exception;
        }
    }
}
