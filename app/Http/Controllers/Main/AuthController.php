<?php

namespace App\Http\Controllers\Main;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
//use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {

    public function show() {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json($user, Response::HTTP_OK);
    }

    public function store(Request $request) {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'phone' => 'required|string|max:30',
            ]);

            $user = User::create([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'phone' => $request->phone,
                        'active' => true,
                        'super' => false,
            ]);

            // Mail::to($user->email)->send(new WelcomeEmail($user));

            return response()->json(['succes' => 'Usuário cadastrado com sucesso', 'user' => $user], Response::HTTP_CREATED );
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Erro ao registrar usuário'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao registrar usuário', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request) {
        // Função para atualizar o usuário logado

        try {
            // Validar os dados recebidos, excluindo a validação da senha
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'email' => 'email|unique:users,email,' . auth()->id(),
                    // Adicione aqui outras regras de validação para outros campos, se necessário
            ]);

            // Identificar o usuário logado usando JWT
            $user = JWTAuth::parseToken()->authenticate();

            // Atualizar os dados do usuário com os novos valores
            $user->fill($validatedData);
            $user->save();

            // Retornar uma resposta de sucesso
            return response()->json(['message' => 'Usuário atualizado com sucesso'], Response::HTTP_ACCEPTED);
        } catch (\Throwable $th) {
            // Lidar com exceções, como retornar uma resposta de erro
            return response()->json(['error' => 'Erro interno do servidor'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_token(Request $request) {
        try {
            // Lógica para obter um token de acesso
        
            $dataRequest = $request->only(['email', 'password']);
           
            $pass = base64_decode($dataRequest['password']);
            
            $email = base64_decode($dataRequest['email']);
            
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['error' => 'Credenciais inválidas'], Response::HTTP_UNAUTHORIZED);
            }

            // Verifique se o usuário está ativo
            if (!$user->active) {
                return response()->json(['error' => 'Usuário não está ativo'], Response::HTTP_UNAUTHORIZED);
            }

            $credentials = [
                'email' => $email,
                'password' => $pass
            ];
           
            // Verifique se as credenciais são válidas
            if (!Auth::validate($credentials)) {
                return response()->json(['error' => 'Credenciais inválidas'], Response::HTTP_UNAUTHORIZED);
            }
      
        
            // Tente gerar o token JWT
            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => 'Falha ao tentar se autenticar'], Response::HTTP_BAD_REQUEST);
            }
        
            // Informações adicionais para o token
            $additionalData = [
                'user' => $user
            ];
        
            // Adicione as informações adicionais ao token
            $tokenWithAdditionalData = auth('api')->claims($additionalData)->attempt($credentials);
        
            return $this->respondWithToken($tokenWithAdditionalData, $additionalData);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro interno do servidor', 'error'=> $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);}
    }

    public function update_password(Request $request) {
        // Função para atualizar senha do usuário
        try {
            // Validar os dados recebidos
            $request->validate([
                'password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // Identificar o usuário logado usando JWT
            $user = JWTAuth::parseToken()->authenticate();


            // Verificar se a senha atual fornecida é válida
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Senha atual incorreta'], 400);
            }

            // Atualizar a senha do usuário com a nova senha fornecida
            $user->password = bcrypt($request->new_password);
            $user->save();

            // Retornar uma resposta de sucesso
            return response()->json(['message' => 'Senha atualizada com sucesso'], 202);
        } catch (\Throwable $th) {
            // Lidar com exceções, como retornar uma resposta de erro
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function reset_password(Request $request) {
        // Função para resetar senha do usuário
    }

    public function check_token(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json( $user, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro interno do servidor', 'error'=> $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }}


    protected function respondWithToken($token, $additionalData) {
        return response()->json([
                    'token' => $token,
                    'data' => $additionalData,
                    'type' => 'bearer',
                        ], Response::HTTP_OK);
    }

}
