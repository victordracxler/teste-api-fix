<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PessoaStoreRequest;
use App\Http\Requests\PessoaUpdateRequest;
use App\Services\CepService;
use App\Services\PessoaServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PessoaController extends Controller
{
    /**
     * @var PessoaServiceInterface
     */
    private $pessoaService;
    private $cepService;

    public function __construct(PessoaServiceInterface $pessoaService, CepService $cepService)
    {
        $this->pessoaService = $pessoaService;
        $this->cepService = $cepService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pessoas = $this->pessoaService->all();
        if ($pessoas) {
            return response()->json($pessoas, Response::HTTP_OK);
        }
        return response()->json($pessoas, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PessoaStoreRequest $request)
    {
        $cepValido = $this->cepService->validaCep($request->input("cep"));
        if (!$cepValido) {
            return response()->json(['message' => "CEP inválido"], Response::HTTP_BAD_REQUEST);
        }

        $pessoa = $this->pessoaService->create($request->all());
        if ($pessoa) {
            return response()->json($pessoa, Response::HTTP_OK);
        }
        return response()->json($pessoa, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $pessoa = $this->pessoaService->find($id);
        if ($pessoa) {
            return response()->json($pessoa, Response::HTTP_OK);
        }
        return response()->json($pessoa, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(PessoaUpdateRequest $request, $id)
    {
        $cepValido = $this->cepService->validaCep($request->input("cep"));
        if (!$cepValido) {
            return response()->json(['message' => "CEP inválido"], Response::HTTP_BAD_REQUEST);
        }

        $pessoa = $this->pessoaService->find($id);
        if (!$pessoa) {
            return response()->json(['message' => "Pessoa não encontrada"], Response::HTTP_NOT_FOUND);
        }

        $pessoaUpdate = $this->pessoaService->update($request->all(), $id);
        if ($pessoaUpdate) {
            return response()->json($pessoaUpdate, Response::HTTP_OK);
        }
        return response()->json($pessoaUpdate, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pessoa = $this->pessoaService->find($id);
        if (!$pessoa) {
            return response()->json(['message' => "Pessoa não encontrada"], Response::HTTP_NOT_FOUND);
        }

        $pessoaDelete = $this->pessoaService->delete($id);

        if ($pessoaDelete) {
            return response()->json(['message' => "Pessoa excluída com sucesso!"], Response::HTTP_OK);
        }
        return response()->json($pessoaDelete, Response::HTTP_BAD_REQUEST);

    }
}
