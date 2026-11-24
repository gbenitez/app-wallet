# Wallet API API REST en Laravel para transferencias entre cuentas, con transacciones y bloqueo pesimista. 
## Requisitos- PHP 8.2+ con extensión bcmath- Composer 2- MySQL 8 
## Instalación    
git clone https://github.com/TU_USUARIO/wallet-api.git    
cd wallet-api    
composer install    
cp .env.example .env    
php artisan key:generate    
# configurar DB_* en .env    
php artisan migrate --seed   
php artisan serve 
## Pruebas    
php artisan test 
## Endpoints 
| Método | Ruta | Descripción | 
|---|---|---| 
| GET | /api/accounts/{id} | Cuenta y saldo | 
| GET | /api/accounts/{id}/transfers | Historial paginado | 
| POST | /api/transfers | Crear transferencia | 

Ejemplo:    
curl -X POST http://127.0.0.1:8000/api/transfers \      
-H "Accept: application/json" -H "Content-Type: application/json" \      
-d '{"from_account_id":1,"to_account_id":2,"amount":25000}' 

## Decisiones técnicas
- Montos en decimal(15,2) y operaciones con bcmath para no perder centavos.
- DB::transaction + lockForUpdate para evitar saldos negativos en peticiones simultáneas.
- Bloqueo de cuentas en orden de id para evitar deadlocks.
- Las transferencias fallidas se registran fuera de la transacción para que el rollback no las borre.
- Lógica de negocio en TransferService; 
controladores delgados; Form Requests y API Resources. 
## Supuestos
- (anota aquí lo que decidiste ante ambigüedades del enunciado)