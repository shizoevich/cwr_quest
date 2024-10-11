<h1 align="center">
 :ledger:  Change Within Reach
</h1>

## Table of contents

- [General](#general1)
- Database Schema
- Architecture
  - [BackEnd](#backEnd)**`(Folder Structure)`**
  - [FrontEnd](#frontEnd)**`(Folder Structure)`**
- [Parsers](#parsers)
- [Services](#general2)
  - [Generate Gmail Token](#generateGmailToken) for **` Tridiuum 2fa`**
  
## General info ℹ️ <a name="general1"></a>
### 🖍 Requirements:

- Laravel 5.4
- MySQL (5.6);
- Vue (2.6.10);
- React(17.x);
- Node (12.x.x);
- NPM (6.x.x);
- Redis
- PM2(daemon process manager)

## Commits

This project follows the [Conventional Commits](https://www.conventionalcommits.org/) specification

## ⚙️ BackEnd <a name="backEnd"></a>

For the [BackEnd](./src) to work properly, you need to fill in the **`.env`** file. You can use the **`.env.example`** file as an example.a

**Folder Structure you need to know**
| Folder name | functional responsibility |
| ------ | ------ |
| src\app\Channels | RingcentralSmsChannel for Notification  |
| src\app\Components | 1)PatientForm 2)Doctors Salary 3)Snooze Notification 4)Square (connection and forms) |
| src\app\Console | Core console is made for run pasers, change credentials, crud functionality for Officeally and Tridiuum service|
| src\app\Contracts | Contract pattern|
| src\app\DTO | DTO pattern for Officeally |
| src\app\Enums\Ringcentral | const types for service Ringcentral  |
| src\app\Events | Almost events using for parsers, especially for Officeally and Tridiuum |
| src\app\Exceptions | Custom exception for Goolge, Officeally, Square, Ringcentral  services |
| src\app\Helpers | Additional functions for all services for CWR project |
| src\app\Http | API and HTTP Controllers, Custom Request Validation, Custom Request Validation for logging, WebHooksController |
| src\app\Jobs | Almost parsers we use for our next service - Tridiuum, Ringcentral,Goolge, Officeally,Twilio |
| src\app\Mail | Custom mail telehealth invite, Tridiuum, google access to account |
| src\app\Models | Models, we have in folder **app/Models** and in **folder app**|
| database | We have only Migrations and Seeding|
| public | We have react-app(react component build),images,js(vue.js build), swagger doc for api(folder - swagger-resources, api-docs)|
| resources| We have front, made by **blade.php** and **vue.js**(resources/assets/js) |
| routes | We have three types of routes - api, web and channels(we use Laravel Echo Server)|
| storage | We have parsers logs about actions and errors and common log |

## 💡 FrontEnd <a name="frontEnd"></a>

- Old FrontEnd is in [Folder](./src/resources/assets/js);
- New FrontEnd is in [Folder](./src/public/react-app) but, you should make build 
  from next Repository http://gitlab.groupbwt.com/root/cwr-front;

**Folder And File Structure**
| Folder name | functional responsibility |
| ------ | ------ |
| components | vue.js components for global and local using |
| helpers | additional functions |
| mixins | different approach to use appointment functionality |
| officeally | edit appointments |
| settings | const Officeally and Square |
| store | use to load chart |
| forms-routes.js and routes.js | routing in two different places for our frontEnd |
| store.js and forms-store.js  | store pattern in two different places |
| forms-app.js and app.js | entry points for our project |

## 🏃‍♂️ Simple start

- **`cp .env.example .env** at the root **src ** 
- **`composer install`** at the root **src ** 
- **`php artisan key generate`** at the root **src ** 
- Old FrontEnd  **`npm install&npm run prod`** at the root **src ** 
- New FrontEnd **React App Repository** http://gitlab.groupbwt.com/root/cwr-front - you should make build **`npm install&npm run build`** 
- New FrontEnd **copy files and folder** from build(**asset-manifest.json**, **index.html**, folder - **static **) to next [Folder](./src/public/react-app)
- Enjoy

## :movie_camera: Parsers <a name="parsers"></a> |  
-  Link to [officeally-integration](./doc/officeally-integration.md) 
-  Link to [ringcentral-integration](./doc/ringcentral-integration.md) 
-  Link to [tridiuum-integration](./doc/tridiuum-integration.md) 


## Services <a name="general2"></a>

###  🛠 Generate gmail token <a name="generateGmailToken"></a> for Tridiuum 2fa ℹ️ 

Attention!!! You must be in account appointments@changewithinreach.care to make token !!!

1. Open console and write artisan command
   ```sh
   php artisan token:generate
   ```
2. Then, you will see in console next info
   ```sh
   'Open the following link in your browser:'
   'https://accounts.google.com/o/oauth2/auth?response_type=code&access_type=online'
   ```
3. You must copy text and paste in url browser
   ```sh
   by start from 'https://accounts.google.com/'   and to the end  'https://accounts.google.com/'
   ```
4. You will see your gmail account, the next action  - allow to use service by this email
5. After that you will be redirected to route, that you assign before for gmail credentials 'REDIRECT URI'


