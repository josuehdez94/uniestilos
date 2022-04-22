<?php

// src/AppBundle/Utils/MonologDBHandler.php

namespace App\Generales;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;

class MonologDBHandler extends AbstractProcessingHandler
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * MonologDBHandler constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * Called when writing to our database
     * @param array $record
     */
    protected function write(array $record): void
    {
        try {
            //echo "Error guardado exitosamente:" . $record['level'] . '-' . $record['message'];
            if($record['level'] != 200 && $record['level'] != 100 ){
                $logEntry = new Log();
                $logEntry->setMessage($record['message']);
                $logEntry->setLevel($record['level']);
                $logEntry->setLevelName($record['level_name']);
                if (is_array($record['extra'])) {
                    $logEntry->setExtra(implode(', ', $record['extra']));
                }

                if (is_array($record['context'])) {
                    $logEntry->setExtra(implode(', ', $record['context']));
                }


                $this->em->persist($logEntry);
                $this->em->flush();
            }
        } catch (\Exception $e) {
            //echo "error al guardar error" . $e;
        }
    }

}
