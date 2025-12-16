# Creación principal

## Tabla Commerces (commerces)

| Campo           | Tipo          | Valor             |
| --------------- | ------------- | ----------------- |
| owner_user_id   | string        | `1`              |
| department_id   | integer       | `1`               |
| city_id         | integer       | `1`               |
| neighborhood_id | integer       | `1`               |
| name            | string        | `"Restaurante el buen comer"`              |
| description     | string        | `"No hay observaciones"`              |
| tax_id          | integer       | `1`               |
| tax_id_type     | string        | `CC`              |
| address         | string        | `CRA 67 # 45-69`  |
| phone           | string        | `3121234567`      |
| email           | string        | `email@gmail.com` |
| is_verified     | boolean / int | `0`               |
| is_active       | boolean / int | `1`               |

## Tabla Legal Representatives (legal_representatives)

| Campo         | Tipo           | Valor                    |
| ------------- | -------------- | ------------------------ |
| commerce_id   | bigInt or null | `null`                   |
| name          | string         | `Pablo`                  |
| last_name     | string         | `Garcia`                 |
| document      | string         | `1089789`                |
| document_type | enum         | `CC`                     |
| email         | string         | `pablo.GARCIA@gmail.com` |
| phone         | string         | `3198901278`             |
| is_primary    | boolean / int  | `1`                      |

## Tabla Commerce Documents -> Tabla aún no creada

| Campo         | Tipo              | Valor   |
| ------------- | ----------------- | ------- |
| verified_by_id (user_id)  | bigInt           | `1`     |
| uploaded_by_id (user_id) | bigInt           | `1`     |
| document_type | enum            | `""`    |
| file_path     | string            | `""`    |
| mime_type     | string            | `""`    |
| verified      | boolean           | `false` |
| uploaded_at   | datetime or string | `""`    |
| verified_at   | datetime or null   | `null`  |
