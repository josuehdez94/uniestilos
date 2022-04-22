<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#entidades
use App\Entity\Log;

/**
 * @Route("/backend/logs")
 */
class LogController extends AbstractController {

    /**
     * @version esop.190620.1210 Se aumenta a 100 el paginador
     * 
     * @Route("/", name="backend_log_index", methods={"GET"})
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //$logs = $em->getRepository(Log::class)->createQueryBuilder('l')->orderBy('l.createdAt', 'DESC');        
        $logs = $em->getRepository(Log::class)->indexLog();        
        /* $logsPaginados = $paginator->paginate(
                $logs,
                $request->query->getInt('pagina', 1),
                100
        );   */      
        return $this->render('backend/Log/index_log.html.twig', [
                    'logs' => $logs
        ]);
    }

    /**
     * @Route("/errores/{tipo_error}", name="backend_log_criticos", methods={"GET"})
     */
    public function erroresClasificados(Request $request, PaginatorInterface $paginator, $tipo_error){
        $em = $this->getDoctrine()->getManager();
        if($tipo_error == 'criticos'){
            $error = 'CRITICAL';
        }elseif ($tipo_error == 'no-criticos'){
            $error = 'ERROR';
        }elseif ($tipo_error == 'todos'){
            $error = 'todos';
        }
        if(!isset($error)){
            throw $this->createNotFoundException('Tipo de error no encontrado');
        }
       
        //$error == 'todos' ? $logs = $em->getRepository(Log::class)->createQueryBuilder('l')->orderBy('l.createdAt', 'DESC') : $logs = $em->getRepository(Log::class)->createQueryBuilder('l')->where('l.levelName = :tipo')->setParameter('tipo', $error)->orderBy('l.createdAt', 'DESC');
        $error == 'todos' ? $logs = $em->getRepository(Log::class)->indexLog()  : $logs = $em->getRepository(Log::class)->erroresTipoLog($error);
        $logsPaginados = $paginator->paginate(
            $logs,
            $request->query->getInt('pagina', 1),
            100
        );
        return $this->render('Backend/Log/logCriticos.html.twig', [
            'logs' => $logsPaginados,
            'tipoError' => $tipo_error
        ]);
    }

}
