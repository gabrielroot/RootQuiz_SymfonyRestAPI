<?php

namespace App\Controller;

use App\Entity\Pergunta;
use App\Entity\Resposta;
use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class PerguntaController extends AbstractController
{
    /**
     * @Route("/perguntas", name="index", methods="GET")
     */
    public function index(): Response{
        $query = $this->getDoctrine()
            ->getRepository(Pergunta::class)
            ->getPerguntasRespostas();

        $perguntas = array();
        foreach ($query as $item){
            $alternativas = array('A','B','C','D');
            $respostas = array();
            $i = 0;
            foreach ($item->getRespostas() as $valor){
                $respostas[] = [
                    'id'=> $valor->getId(),
                    'alternativa'.$alternativas[$i]=> $valor->getAlternativa()
                ];
                $i++;
            }
            $perguntas[] = array(
              'id'=>$item->getId(),
              'respostaCorreta'=>$item->getRespostaCorreta(),
              'questao'=>$item->getQuestao(),
                'Respostas'=>$respostas
            );
        }

        return $this->json($perguntas);
    }

    /**
     * @Route("/pergunta", name="save", methods="POST")
     */
    public function save(Request $request): Response{
        $usuarioRepository = $this->getDoctrine()->getRepository(Usuario::class);

        $body =  $request->toArray();
        $entityManager = $this->getDoctrine()->getManager();

        $usuarioEncontrado = $usuarioRepository->find($body['usuario']);
        if(is_null($usuarioEncontrado)){
            return $this->json(["Erro"=>'Usuário não encontrado']);
        }

        $pergunta = new Pergunta();
        $pergunta->setQuestao($body['questao']);
        $pergunta->setRespostaCorreta($body['respostaCorreta']);
        $pergunta->setUsuario($usuarioEncontrado);

        $entityManager->persist($pergunta);
        $entityManager->flush();

        for($i=0; $i < sizeof($body['respostas']); $i++){
            $resposta = new Resposta();
            $resposta->setAlternativa($body['respostas'][$i]);
            $resposta->setPergunta($pergunta);
            $entityManager->persist($resposta);
            $entityManager->flush();
        }

        return $this->json(["Sucess"=>"OK"]);
    }

    /**
     * @Route("/pergunta/{id}", name="update", methods="PUT")
     */
    public function update(Request $request, int $id): Response{
        $usuarioRepository = $this->getDoctrine()->getRepository(Usuario::class);
        $perguntaRepository = $this->getDoctrine()->getRepository(Pergunta::class);

        $body =  $request->toArray();
        $entityManager = $this->getDoctrine()->getManager();

        $perguntaEncontrada = $perguntaRepository->find($id);
        if(is_null($perguntaEncontrada)){
            return $this->json(["Erro"=>'Pergunta não encontrada']);
        }

        $usuarioEncontrado = $usuarioRepository->find($body['usuario']);
        if(is_null($usuarioEncontrado)){
            return $this->json(["Erro"=>'Usuário não encontrado']);
        }

        $perguntaEncontrada->setQuestao($body['questao']);
        $perguntaEncontrada->setRespostaCorreta($body['respostaCorreta']);
        $perguntaEncontrada->setUsuario($usuarioEncontrado);

        $entityManager->persist($perguntaEncontrada);
        $entityManager->flush();

        return $this->json(["Sucess"=>"OK"]);
    }

    /**
     * @Route("/pergunta/{id}", name="delete", methods="DELETE")
     */
    public function delete(int $id): Response{
        $perguntaRepository = $this->getDoctrine()->getRepository(Pergunta::class);

        $entityManager = $this->getDoctrine()->getManager();

        $perguntaEncontrada = $perguntaRepository->find($id);
        if(is_null($perguntaEncontrada)){
            return $this->json(["Erro"=>'Pergunta não encontrada']);
        }

        $entityManager->remove($perguntaEncontrada);
        $entityManager->flush();

        return $this->json(["Sucess"=>"OK"]);
    }
}