<?php
namespace Asterisk;

use Ratchet\ConnectionInterface;
use PAMI\Message\Event\EventMessage;
use PAMI\Message\Event\NewstateEvent;
use PAMI\Message\Event\HangupEvent;
use PAMI\Message\Event\OriginateResponseEvent;

class Client {
    /**
     * Последнее сообщения
     * @var PAMI\Message\Event\EventMessage
     */
    private $message;
    /**
     * Соединение с сокетом
     * @var Ratchet\ConnectionInterface
     */
    private $connection;
    /**
     * Идентификатор телефонной линии
     * @var string
     */
    private $id;
    /**
     * Дата последней активности. Не используется
     * @var int
     */
    private $lastactive;

    public function __construct(ConnectionInterface $connection, $id=NULL) {
        $this->connection = $connection;

        if ($id) {
            $this->id = $id;
        }

        $this->lastactive = time();
    }

    function getConnection() {
        return $this->connection;
    }

    function setConnection($connection) {
        $this->connection = $connection;
    }

    function closeConnection() {
        $this->connection->close();
        $this->connection = NULL;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage(EventMessage $message) {
        $this->message = $message;
        $this->process();
    }

    public function process() {
        if (!$this->connection || !$this->message) {
            return;
        }

        if ($this->message instanceof NewstateEvent) {
            $message = array('event' => 'incoming',
                'value' => $this->message->getConnectedLineNum());
        } elseif ($this->message instanceof HangupEvent) {
            $message = array('event' => 'hangup');
        } else {
            return;
        }

        $json = json_encode($message);
        $this->connection->send($json);
    }

    function getId() {
        return $this->id;
    }

    function setId($id) {
        $this->id = $id;
    }
}
?>

