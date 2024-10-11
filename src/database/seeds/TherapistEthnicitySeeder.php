<?php

use App\Models\Therapist\TherapistSurveyEthnicity;
use Illuminate\Database\Seeder;

class TherapistEthnicitySeeder extends Seeder
{
    private $ethnicities = [
        [
            'tridiuum_value' => '408e27ed-1fb3-40ed-8da1-5fe9a58bb49a',
            'label' => 'Acadian/Cajun'
        ],
        [
            'tridiuum_value' => '7be302c2-43db-43ec-9c7e-8d9d97167147',
            'label' => 'Afghan/Afghanistani'
        ],
        [
            'tridiuum_value' => '3535a9e9-a3ef-469e-975d-34b2b53de529',
            'label' => 'Agikuyu/Kikuyu'
        ],
        [
            'tridiuum_value' => 'c0d8255a-e619-40de-a426-ad43c74e6937',
            'label' => 'Akan'
        ],
        [
            'tridiuum_value' => '3fb0558d-db35-43da-9b5f-3fb4e619c7fb',
            'label' => 'Alaska Athabascan'
        ],
        [
            'tridiuum_value' => '73d56e5e-f20f-463f-968c-3ac137286d57',
            'label' => 'Albanian'
        ],
        [
            'tridiuum_value' => '8d8c9504-896f-4323-be57-5364872397c3',
            'label' => 'Aleut'
        ],
        [
            'tridiuum_value' => 'be64a7b5-32b5-4720-825f-4a7195e0216a',
            'label' => 'Algerian'
        ],
        [
            'tridiuum_value' => 'b8b11adb-9643-4b4c-9bfc-bb42b0a7e262',
            'label' => 'Alsatian'
        ],
        [
            'tridiuum_value' => 'ac705f8c-336a-4722-bd59-dd00e12fe148',
            'label' => 'Amara/Amhara'
        ],
        [
            'tridiuum_value' => 'f418d559-998b-4e1a-9b6d-a8b84859e401',
            'label' => 'Amazigh/Imazighen/Berber'
        ],
        [
            'tridiuum_value' => 'ec9f9370-5e37-4cc4-a11f-b0ba064ba218',
            'label' => 'American/United States'
        ],
        [
            'tridiuum_value' => '512fc08e-5542-4f2f-8102-4ebd59a2f119',
            'label' => 'Amerindian/Indigena/Indio'
        ],
        [
            'tridiuum_value' => 'c96f8e90-d3e8-4c24-9160-7115b2f05fc2',
            'label' => 'Antiguan/Barbudan'
        ],
        [
            'tridiuum_value' => '91bfc43d-a6df-4d5d-b27f-7199c25db412',
            'label' => 'Apache'
        ],
        [
            'tridiuum_value' => 'bc5c0b67-b388-4db4-9422-f7527a255c7b',
            'label' => 'Arab/Arabic'
        ],
        [
            'tridiuum_value' => '13415a81-8388-417f-8bbc-3d20db5c6dcd',
            'label' => 'Argentine/Argentinean'
        ],
        [
            'tridiuum_value' => 'cf92c326-9754-4ee9-a465-bb710c8be9a3',
            'label' => 'Armenian'
        ],
        [
            'tridiuum_value' => 'cb5a8cd8-f32f-4672-a2a6-8ecde72e0d19',
            'label' => 'Asian Indian/Indian (Asia)'
        ],
        [
            'tridiuum_value' => 'fa33ea03-b8b0-49a3-b2f7-201d1146da73',
            'label' => 'Assyrian/Chaldean/Syriac'
        ],
        [
            'tridiuum_value' => '30a4012b-b509-4edd-aafc-39ece5b4b0c0',
            'label' => 'Australian'
        ],
        [
            'tridiuum_value' => '04796f86-8793-495d-b571-070a4f11a617',
            'label' => 'Austrian'
        ],
        [
            'tridiuum_value' => '0778f483-959f-4a66-ae03-9e80d87210c2',
            'label' => 'Azerbaijani'
        ],
        [
            'tridiuum_value' => 'ba0f1051-10c7-49ba-b29c-aa6048059d41',
            'label' => 'Azeri'
        ],
        [
            'tridiuum_value' => '601847f6-9726-4145-98b1-6b95bf63f7a9',
            'label' => 'Bahamian'
        ],
        [
            'tridiuum_value' => '01d6a205-c6b6-4f66-8be7-59c411bf605f',
            'label' => 'Bajan/Barbadian'
        ],
        [
            'tridiuum_value' => '6a01a5b0-c7ed-426b-9743-9c33165fb0a0',
            'label' => 'Bamar/Burman'
        ],
        [
            'tridiuum_value' => 'c0bf898c-ac07-411f-b061-edfd573320ff',
            'label' => 'Bangladeshi'
        ],
        [
            'tridiuum_value' => 'fcbefd8b-e302-4b95-9ecd-831d24a9c156',
            'label' => 'Bantu'
        ],
        [
            'tridiuum_value' => '70f12655-0849-4517-bb63-415be6af3992',
            'label' => 'Basque'
        ],
        [
            'tridiuum_value' => 'dfb9983a-c685-4b50-a276-d49173599c13',
            'label' => 'Belarusian/Belorussian'
        ],
        [
            'tridiuum_value' => '2c53da5e-d7e5-4559-8242-c9a2e9c889bf',
            'label' => 'Belgian'
        ],
        [
            'tridiuum_value' => 'd0f1e1fb-3a58-4c14-8a0d-f58297dc9252',
            'label' => 'Belizean'
        ],
        [
            'tridiuum_value' => '63e31191-1b76-4a3a-ab18-07f5f4ead724',
            'label' => 'Bengali'
        ],
        [
            'tridiuum_value' => '50da047c-e694-4f5b-93d5-3457e28a3404',
            'label' => 'Blackfeet'
        ],
        [
            'tridiuum_value' => 'fc10a5a3-6132-4c2f-8e21-97351eea2927',
            'label' => 'Bolivian'
        ],
        [
            'tridiuum_value' => '517d7d5a-ccd0-4f4d-b7b2-b653d0f7ea05',
            'label' => 'Bosniak'
        ],
        [
            'tridiuum_value' => 'ec3adb55-bff0-493e-8128-81634dd6c491',
            'label' => 'Bosnian/Herzegovinian'
        ],
        [
            'tridiuum_value' => '9babeaf9-9121-4cda-8bdd-c3c402e265a7',
            'label' => 'Brazilian'
        ],
        [
            'tridiuum_value' => 'e7427c44-6f00-4f5f-b74f-cb445cf60389',
            'label' => 'British Isles/British Isles origin'
        ],
        [
            'tridiuum_value' => 'd225e0c1-747a-44ed-a283-f88a36e6714e',
            'label' => 'British West Indian/Indies'
        ],
        [
            'tridiuum_value' => '277cab5b-da92-42ed-bcaf-6518161f1a88',
            'label' => 'Briton/British'
        ],
        [
            'tridiuum_value' => '7b21a65e-ec3b-40c7-853a-b31ec7c177cb',
            'label' => 'Bulgarian'
        ],
        [
            'tridiuum_value' => '8a333ae4-0ab9-4bee-bf3a-045abf8d6ed1',
            'label' => 'Burmese'
        ],
        [
            'tridiuum_value' => 'fa9a213b-b077-409d-8daa-3e597bf2e3f3',
            'label' => 'Cambodian'
        ],
        [
            'tridiuum_value' => '5ca48226-4548-45d0-b490-147bfb9943a2',
            'label' => 'Cameroonian/Cameroon'
        ],
        [
            'tridiuum_value' => '6af060be-2c8b-4b0c-8361-9ae264719603',
            'label' => 'Canadian'
        ],
        [
            'tridiuum_value' => '8b90f5f6-56d4-466a-8afc-ddf0caf750f2',
            'label' => 'Canadian American Indian'
        ],
        [
            'tridiuum_value' => 'a608fc57-db55-43d6-bbb9-81d8c83b0f44',
            'label' => 'Cape Verdean'
        ],
        [
            'tridiuum_value' => '3d70e048-8642-46d6-8132-cfc7f5349264',
            'label' => 'Carpatho Rusyn'
        ],
        [
            'tridiuum_value' => 'c29f93b9-4183-48c9-a7d4-24f8aef17db4',
            'label' => 'Celtic'
        ],
        [
            'tridiuum_value' => '7eaf26ff-ff86-4c15-9f2a-441c8107a50b',
            'label' => 'Central American Indian'
        ],
        [
            'tridiuum_value' => '11c77180-84a9-458c-873d-0de830dc63e5',
            'label' => 'Central American (Nos)'
        ],
        [
            'tridiuum_value' => '125ea489-0b68-4122-a745-00a9d6a0b9d4',
            'label' => 'Cherokee'
        ],
        [
            'tridiuum_value' => 'cbb9116c-0cf1-48b4-9eb6-dbbbb3f8c97a',
            'label' => 'Cheyenne'
        ],
        [
            'tridiuum_value' => '52aaa12a-a286-4f1c-a8f6-2d4a81037fb0',
            'label' => 'Chickasaw'
        ],
        [
            'tridiuum_value' => 'c739a55b-92de-48fd-aae7-626269069adb',
            'label' => 'Chilean'
        ],
        [
            'tridiuum_value' => '741140fd-c9ba-4c5e-8e87-49014980d231',
            'label' => 'Chinese'
        ],
        [
            'tridiuum_value' => '10469a1e-2339-492f-befd-7e44ef84c1e3',
            'label' => 'Chippewa'
        ],
        [
            'tridiuum_value' => 'b51891a2-3287-4e78-af19-d89a7e78ce80',
            'label' => 'Choctaw'
        ],
        [
            'tridiuum_value' => '8aaec2cc-b93b-4683-83b1-183039a8124e',
            'label' => 'Colombian'
        ],
        [
            'tridiuum_value' => '403a18fb-ad18-4be2-a85e-b89236a9e0be',
            'label' => 'Colville'
        ],
        [
            'tridiuum_value' => '8b5dd62d-b98f-40df-9936-c805090db9ef',
            'label' => 'Comanche'
        ],
        [
            'tridiuum_value' => '85d372f2-20bb-4c67-93df-90bc38f07e1b',
            'label' => 'Congolese/Congo'
        ],
        [
            'tridiuum_value' => '17656bd9-dd85-4655-af3a-466efba164aa',
            'label' => 'Costa Rican'
        ],
        [
            'tridiuum_value' => 'd5eb17ab-3441-4026-83a8-0ea5b8928dc0',
            'label' => 'Creek'
        ],
        [
            'tridiuum_value' => '56eaefc9-4073-427d-bd1e-b2b0551e8a4b',
            'label' => 'Creole'
        ],
        [
            'tridiuum_value' => 'ec598418-08b7-483b-b36a-3acff0750c51',
            'label' => 'Croat/Croatian'
        ],
        [
            'tridiuum_value' => '1f3dac90-ed2e-4755-af37-8a719103d500',
            'label' => 'Crow'
        ],
        [
            'tridiuum_value' => '0f454486-37fb-4cf6-b446-48e7abda722a',
            'label' => 'Cuban'
        ],
        [
            'tridiuum_value' => 'd5ba7d17-1fc2-4dc5-a920-1c41295fcdd4',
            'label' => 'Cypriot'
        ],
        [
            'tridiuum_value' => 'bd6d2f6b-46c0-4220-9b2c-b3f80cb0bd4e',
            'label' => 'Czech'
        ],
        [
            'tridiuum_value' => '009e0c93-b4ac-4813-bbda-f691949e1c90',
            'label' => 'Czechoslovakian'
        ],
        [
            'tridiuum_value' => 'b1fb3db0-f2c9-4135-ae93-319acbdeeaca',
            'label' => 'Dane/Danish'
        ],
        [
            'tridiuum_value' => '49fe6a9f-121b-4ee8-95d3-ff4f5297aed2',
            'label' => 'Delaware'
        ],
        [
            'tridiuum_value' => '80755177-8cf1-4516-92ce-61d07794dd61',
            'label' => 'Dominican'
        ],
        [
            'tridiuum_value' => 'b8e3d103-89c5-408e-ac21-465db433d6dd',
            'label' => 'Dutch'
        ],
        [
            'tridiuum_value' => 'b7445a29-0430-41c1-bb02-c9857631a84a',
            'label' => 'Dutch West Indian/Indies'
        ],
        [
            'tridiuum_value' => '543312ad-75ec-4f81-ba83-07e2bbd9c501',
            'label' => 'Eastern Cherokee'
        ],
        [
            'tridiuum_value' => 'f96e3c24-a204-4c63-b7c1-01c75dbfba1c',
            'label' => 'East Indian'
        ],
        [
            'tridiuum_value' => '8a959ea4-0e64-4746-abfe-197a26bd9da6',
            'label' => 'Ecuadorian'
        ],
        [
            'tridiuum_value' => 'fa12d3c4-82a5-42a3-b315-37be1cae1742',
            'label' => 'Egyptian'
        ],
        [
            'tridiuum_value' => '78bf3133-dd7e-4e5d-8d1c-543dd3dfbe83',
            'label' => 'Emirati/United Arab Emirates'
        ],
        [
            'tridiuum_value' => 'aa313e15-1aba-4a7d-b616-fff5d119f842',
            'label' => 'English'
        ],
        [
            'tridiuum_value' => '39b64ef2-6fef-4d50-b555-280241183acd',
            'label' => 'Eritrean'
        ],
        [
            'tridiuum_value' => '6bfeae17-ba02-4785-a313-25ac75d292c3',
            'label' => 'Eskimo'
        ],
        [
            'tridiuum_value' => '0462de4a-a933-48f1-bd70-622c64d2ff8c',
            'label' => 'Estonian'
        ],
        [
            'tridiuum_value' => 'f41d9abb-4a7a-46f3-8f18-d294bfd05b84',
            'label' => 'Ethiopian'
        ],
        [
            'tridiuum_value' => '01bfbeda-f0fb-4fe0-88b1-9ba43da28941',
            'label' => 'Fijian'
        ],
        [
            'tridiuum_value' => '7a3f2ef0-436b-4419-97b0-aa994e19d365',
            'label' => 'Filipino/Philippine'
        ],
        [
            'tridiuum_value' => '397e9497-fa51-475a-b93c-b58884cda096',
            'label' => 'Finn/Finnish'
        ],
        [
            'tridiuum_value' => '1d728263-46ef-4d84-bcce-15444c8b2259',
            'label' => 'Fleming/Flemish'
        ],
        [
            'tridiuum_value' => '9a21ef80-3fae-467f-8986-83177d156f74',
            'label' => 'French'
        ],
        [
            'tridiuum_value' => 'd7ee0136-8b77-4eef-a915-8c4dfa5fc774',
            'label' => 'French Canadian'
        ],
        [
            'tridiuum_value' => '45b0e7a3-6e12-4e40-81ec-7f4783807f40',
            'label' => 'Fulani/Hausa'
        ],
        [
            'tridiuum_value' => 'bcc61521-e970-4b6e-a7ad-8766302f6953',
            'label' => 'Georgian'
        ],
        [
            'tridiuum_value' => 'c9073fc1-336f-4253-b6c9-238f69adf5a8',
            'label' => 'German'
        ],
        [
            'tridiuum_value' => '942ad959-28d8-499d-a16c-806ac13fd507',
            'label' => 'German Russian'
        ],
        [
            'tridiuum_value' => '1de4314c-7739-463a-9660-32bab9d966d7',
            'label' => 'Ghanaian/Ghanian'
        ],
        [
            'tridiuum_value' => '383192c0-9638-459c-85f7-13010175fda3',
            'label' => 'Greek'
        ],
        [
            'tridiuum_value' => '73f36c58-d06b-416f-8850-d4bde5ab1b3a',
            'label' => 'Grenadian'
        ],
        [
            'tridiuum_value' => '70f0ebe7-2c7a-4964-81ce-8b4da3d1dcd0',
            'label' => 'Guamanian/Chamorro'
        ],
        [
            'tridiuum_value' => 'a46d009d-b9ec-4c7a-b527-0f31e577303b',
            'label' => 'Guatemalan'
        ],
        [
            'tridiuum_value' => '70f02145-0546-49cb-a449-418741433372',
            'label' => 'Guyanese'
        ],
        [
            'tridiuum_value' => '6d3522bc-12d7-4aef-b940-ad1a0ccb1fbf',
            'label' => 'Haitian'
        ],
        [
            'tridiuum_value' => 'ccf5228d-4002-414d-ae0c-69874cb98171',
            'label' => 'Hawaiian/Native Hawaiian'
        ],
        [
            'tridiuum_value' => '8c6f0f9a-727f-47f1-b9df-f9aa80a505d8',
            'label' => 'Hispanic/Latino'
        ],
        [
            'tridiuum_value' => '751c5400-eb90-42cc-89a5-0593194f5008',
            'label' => 'Hispanic/Latino - Caribbean Latino'
        ],
        [
            'tridiuum_value' => '8ba82d5b-80bf-439d-974c-6fab545cb8b4',
            'label' => 'Hmong'
        ],
        [
            'tridiuum_value' => '144cb10b-0696-4a8a-9a69-265de9b192e4',
            'label' => 'Honduran'
        ],
        [
            'tridiuum_value' => '8a521478-8184-4100-943a-187abda4b75a',
            'label' => 'Hopi'
        ],
        [
            'tridiuum_value' => '64f3d5ea-0fe3-428a-a46b-ff82dc4ce78a',
            'label' => 'Hungarian'
        ],
        [
            'tridiuum_value' => '03aa73fd-743a-4435-b89a-8126a644cb7b',
            'label' => 'Ibo/Igbo'
        ],
        [
            'tridiuum_value' => '7c0c58dd-c18c-482a-aa64-5ccc94d9e138',
            'label' => 'Icelander'
        ],
        [
            'tridiuum_value' => 'a5611d07-cc6e-4e19-a4de-e00eb33cc55a',
            'label' => 'Indonesian'
        ],
        [
            'tridiuum_value' => 'ba3b16d8-2b6f-4208-9ca8-25990ceab4e7',
            'label' => 'Inupiat Eskimo'
        ],
        [
            'tridiuum_value' => 'bbfba47c-629b-45e5-bc69-98c501b81322',
            'label' => 'Iranian'
        ],
        [
            'tridiuum_value' => '2a620b59-9eed-4476-9944-f3d785249e18',
            'label' => 'Iraqi'
        ],
        [
            'tridiuum_value' => '4369389c-f4bc-4e57-9fe9-a25fd0657e1f',
            'label' => 'Irish'
        ],
        [
            'tridiuum_value' => '7ec80a4c-0e5f-4f00-9f31-183cd23d178a',
            'label' => 'Iroquois'
        ],
        [
            'tridiuum_value' => '3ddbb627-0ab2-45b3-9190-bb6bdcbf2d5f',
            'label' => 'Israeli'
        ],
        [
            'tridiuum_value' => '8686fb78-d21f-4862-94c3-7535e683bba1',
            'label' => 'Italian'
        ],
        [
            'tridiuum_value' => '2c325a3c-a080-445e-96bc-62f9281bcc6d',
            'label' => 'Ivoirian/Ivory Coast'
        ],
        [
            'tridiuum_value' => '4c956fd4-d0d3-4028-bdc7-cb229ff457dc',
            'label' => 'Jamaican'
        ],
        [
            'tridiuum_value' => 'bad52b79-a66a-4c33-bbc7-0ae1633e6490',
            'label' => 'Japanese'
        ],
        [
            'tridiuum_value' => 'c412d7eb-7aa0-4795-a90b-0c3d043f2d5b',
            'label' => 'Javanese/Java/Jawa'
        ],
        [
            'tridiuum_value' => '27b22d20-46f0-4536-afbd-7d2c4b757a1a',
            'label' => 'Jewish'
        ],
        [
            'tridiuum_value' => '7d1a5fdc-054f-432e-b32d-8b09e88ce8f4',
            'label' => 'Jordanian'
        ],
        [
            'tridiuum_value' => '0800dbeb-2e6d-4fb2-ab28-db794c549e3b',
            'label' => 'Kazakh/Qazaq'
        ],
        [
            'tridiuum_value' => 'fdb31a7f-b591-4fb1-b45e-6e8d6a780aab',
            'label' => 'Kazakhstani'
        ],
        [
            'tridiuum_value' => 'aa775f43-3b7e-485e-a161-ebe97a474ab5',
            'label' => 'Kenyan'
        ],
        [
            'tridiuum_value' => '9af2c3ca-bab8-48cd-b98c-f090c9f5e450',
            'label' => 'Keres'
        ],
        [
            'tridiuum_value' => 'fcdf1bf6-2971-4264-8941-c8efe0b9ce28',
            'label' => 'Khmer'
        ],
        [
            'tridiuum_value' => '88f20396-1a92-41ad-95f5-482ef523abbb',
            'label' => 'Kinh/Viet'
        ],
        [
            'tridiuum_value' => 'c127ab0d-6dd0-4295-9e84-67adb38054ad',
            'label' => 'Kiowa'
        ],
        [
            'tridiuum_value' => '213b56db-9bbe-4a7b-bd59-79ad2b336a3e',
            'label' => 'Kittitian/Nevisian'
        ],
        [
            'tridiuum_value' => '0b71eb94-efa2-4fd5-8893-737a03f9028a',
            'label' => 'Korean'
        ],
        [
            'tridiuum_value' => '414951f1-b072-4987-89f5-aee52b6f0999',
            'label' => 'Kurd/Kurdish'
        ],
        [
            'tridiuum_value' => '73e6480d-01aa-49ea-82f9-07b2889b1df4',
            'label' => 'Kuwaiti'
        ],
        [
            'tridiuum_value' => 'd7b06bcd-287f-42c2-8dcb-d00a98856026',
            'label' => 'Lao/Laotian'
        ],
        [
            'tridiuum_value' => '0dc2a10c-f02c-4c04-887f-d2715f74fb61',
            'label' => 'Lao Loum/Lowland Lao'
        ],
        [
            'tridiuum_value' => '8342f1e3-4f79-4d13-805f-eb1a9f4cb967',
            'label' => 'Latin American Indian'
        ],
        [
            'tridiuum_value' => 'cd3bbd4e-4037-4a2b-a1dd-fb39d4f24d10',
            'label' => 'Latvian'
        ],
        [
            'tridiuum_value' => '29de2d5a-bd9f-4b4d-84f0-f36443a58ca9',
            'label' => 'Lebanese'
        ],
        [
            'tridiuum_value' => '2210c62b-036d-4752-b542-ae72758cf59d',
            'label' => 'Liberian'
        ],
        [
            'tridiuum_value' => 'faab9f00-07cd-45b5-9e74-c471bc19c003',
            'label' => 'Libyan'
        ],
        [
            'tridiuum_value' => 'ef368598-f702-450d-a2b0-5e0a7bdc583d',
            'label' => 'Lithuanian'
        ],
        [
            'tridiuum_value' => '4871d7a4-ca55-41b3-b9a9-cbac7955e2d3',
            'label' => 'Lumbee'
        ],
        [
            'tridiuum_value' => '87a25cfc-456a-43d8-9ffc-4bd6cc006fa7',
            'label' => 'Luxemburger'
        ],
        [
            'tridiuum_value' => '68b175bc-230b-402d-84d5-4dbd5078ee3f',
            'label' => 'Macedonian'
        ],
        [
            'tridiuum_value' => 'de7984cd-303b-4302-b2db-3a382c5f9a8d',
            'label' => 'Malay'
        ],
        [
            'tridiuum_value' => '532b9d91-5fd3-4567-aa1e-fe90fe086e32',
            'label' => 'Malaysian'
        ],
        [
            'tridiuum_value' => '10664a5b-4a0f-4bf0-979b-ac5c980503e5',
            'label' => 'Maltese'
        ],
        [
            'tridiuum_value' => 'c0986400-e46d-4f67-a4a3-bd6f4e4448e4',
            'label' => 'Maori'
        ],
        [
            'tridiuum_value' => '69b323b8-42f3-4f9c-91b8-9f77253eca2b',
            'label' => 'Marshallese'
        ],
        [
            'tridiuum_value' => '93058056-ad3f-41f0-9a16-9810c9329f4d',
            'label' => 'Mende'
        ],
        [
            'tridiuum_value' => '448001ea-cd70-4912-be01-09d882b6cf13',
            'label' => 'Mestizo'
        ],
        [
            'tridiuum_value' => 'e4e33f5e-4b42-410b-931a-be04cd082004',
            'label' => 'Mexican American Indian'
        ],
        [
            'tridiuum_value' => 'af10d8b4-1c98-4cd1-8be8-7eeaf1b105f5',
            'label' => 'Mexican/Mex Amer/Chicano'
        ],
        [
            'tridiuum_value' => '3433117c-142b-4fc9-a9e4-6ce805da2757',
            'label' => 'Mohawk'
        ],
        [
            'tridiuum_value' => '737df917-6c7e-4601-a4c5-fe2462cf6340',
            'label' => 'Moldovan/Moldavian'
        ],
        [
            'tridiuum_value' => '2b15ac07-8006-40c4-ac1a-9770d24c2211',
            'label' => 'Montenegrin'
        ],
        [
            'tridiuum_value' => '2eb7be3a-39d3-49a7-8bf7-5abb6c046694',
            'label' => 'Moroccan'
        ],
        [
            'tridiuum_value' => '91360167-71c2-407c-8522-94e53d06a8f9',
            'label' => 'Muscogee (Creek) Nation'
        ],
        [
            'tridiuum_value' => 'ffa95355-d38b-449c-bf4e-d667ec9a1922',
            'label' => 'Navajo'
        ],
        [
            'tridiuum_value' => 'b4086490-2e03-4194-934d-e072ce195bcd',
            'label' => 'Nepalese/Nepali'
        ],
        [
            'tridiuum_value' => '8c1ec354-ef3e-4c4c-998e-a0cb0f9471e0',
            'label' => 'New Zealander/New Zealand'
        ],
        [
            'tridiuum_value' => '0fd30bd3-05f7-4bd4-8357-4f2dc03baeb7',
            'label' => 'Nicaraguan'
        ],
        [
            'tridiuum_value' => '77577b9e-ce2b-4c26-a338-e503ca190a69',
            'label' => 'Nigerian'
        ],
        [
            'tridiuum_value' => '8936c935-0dde-4af3-8428-c5f020f98432',
            'label' => 'Non Hispanic'
        ],
        [
            'tridiuum_value' => 'a4884c43-51ac-4b91-8dbd-b29595fb7c19',
            'label' => 'Norwegian'
        ],
        [
            'tridiuum_value' => '4aad564e-edca-49bd-a46c-bcaf5196461c',
            'label' => 'Oglala Sioux'
        ],
        [
            'tridiuum_value' => '54d6afd0-2e76-4c74-b519-e22aebc0cc7d',
            'label' => 'Okinawan'
        ],
        [
            'tridiuum_value' => '25efc581-f200-45f5-8a69-3b77983320d4',
            'label' => 'Oklahoma Choctaw'
        ],
        [
            'tridiuum_value' => '98d7cce7-e056-4afb-97ec-bb362b5fd312',
            'label' => 'Oneida Nation of New York/Oneida'
        ],
        [
            'tridiuum_value' => '503fe9bf-043a-482f-8fb8-7be1650fd405',
            'label' => 'Oromo'
        ],
        [
            'tridiuum_value' => '46e6e8f3-43c0-4e44-b9af-c65256356bda',
            'label' => 'Osage'
        ],
        [
            'tridiuum_value' => '512d4589-d2fa-49df-bff4-3f9882c08255',
            'label' => 'Other'
        ],
        [
            'tridiuum_value' => 'a24ca1e7-4f10-4baf-ae6b-436f6d2649f1',
            'label' => 'Ottawa'
        ],
        [
            'tridiuum_value' => 'bbfd0347-f064-4944-9f3e-76f5fea18588',
            'label' => 'Paiute'
        ],
        [
            'tridiuum_value' => '051e9f1b-db54-45de-adb0-7ed346970a3e',
            'label' => 'Pakistani'
        ],
        [
            'tridiuum_value' => 'fb4074a8-a788-4bba-bcc1-4026efdf09e5',
            'label' => 'Palestinian'
        ],
        [
            'tridiuum_value' => '5d3a1630-0931-4b49-97a4-4e4e8fcabeb6',
            'label' => 'Panamanian'
        ],
        [
            'tridiuum_value' => '1e2216d1-5bd6-4a30-8b1c-3e25f7d3c364',
            'label' => 'Paraguayan'
        ],
        [
            'tridiuum_value' => '079739da-8cb6-40a3-878a-123a70e6785a',
            'label' => 'Part Hawaiian'
        ],
        [
            'tridiuum_value' => '22a02e6a-fdfa-495c-95f3-67a0deb4df45',
            'label' => 'Pashtun/Pathan'
        ],
        [
            'tridiuum_value' => 'f8580812-13af-4ed5-bcbf-ebeb1998131c',
            'label' => 'Pennsylvania German'
        ],
        [
            'tridiuum_value' => '093582e6-eebb-4849-b9a9-3056dcb84b97',
            'label' => 'Persian'
        ],
        [
            'tridiuum_value' => 'b3dd3dd0-7bd5-4c10-85fc-40706ca980fe',
            'label' => 'Peruvian'
        ],
        [
            'tridiuum_value' => '7cc0a160-6590-4d55-aaaa-82cff67c9d5c',
            'label' => 'Pima'
        ],
        [
            'tridiuum_value' => '30f03d5f-a85f-48cc-bdf0-6be15af343a6',
            'label' => 'Pole/Polish'
        ],
        [
            'tridiuum_value' => '793fa286-be03-4f38-bc75-0364902b4fbe',
            'label' => 'Portuguese'
        ],
        [
            'tridiuum_value' => 'c948a83f-916c-4755-b057-ad4b747c702b',
            'label' => 'Potawatomi'
        ],
        [
            'tridiuum_value' => 'e73e0dcf-dc42-47ee-b985-ba3ba2dc7deb',
            'label' => 'Pueblo'
        ],
        [
            'tridiuum_value' => 'd7fb49b3-d1e6-48e9-be00-a2683cffcf69',
            'label' => 'Puerto Rican'
        ],
        [
            'tridiuum_value' => '775551bf-4970-4f6b-8b29-be18a17bc0e6',
            'label' => 'Puget Sound Salish'
        ],
        [
            'tridiuum_value' => '7e653a5d-c8d1-4ceb-900c-097736c531ea',
            'label' => 'Quechua'
        ],
        [
            'tridiuum_value' => '05a8e00f-b3fd-43b5-9bea-37ef97ae9050',
            'label' => 'Refused'
        ],
        [
            'tridiuum_value' => '8e507eab-7003-4a53-b131-f32ee0f221aa',
            'label' => 'Romanian'
        ],
        [
            'tridiuum_value' => '916c1441-63bf-46be-a9d9-ea1bac946306',
            'label' => 'Rosebud Sioux'
        ],
        [
            'tridiuum_value' => 'a80dd44c-5d9d-4ddd-946c-1201fb88473a',
            'label' => 'Russian'
        ],
        [
            'tridiuum_value' => '23780dc7-1612-4a89-84f7-ae6a6d158b2c',
            'label' => 'Saint Lucian'
        ],
        [
            'tridiuum_value' => 'baafd4a0-db39-46f3-bd91-22dd65850338',
            'label' => 'Salvadoran'
        ],
        [
            'tridiuum_value' => 'f8fc1371-1313-433e-8f18-810826ecd601',
            'label' => 'Samoan'
        ],
        [
            'tridiuum_value' => '974969d0-e9ab-4639-b30f-9c47b90aa210',
            'label' => 'San Carlos Apache'
        ],
        [
            'tridiuum_value' => '6629b2d9-47c5-49f5-9e2e-0b0d87572b0f',
            'label' => 'Saudi/Saudi Arabian'
        ],
        [
            'tridiuum_value' => '25bf2a55-1e63-4dfd-9abf-0ba1d054fc14',
            'label' => 'Sault Ste. Marie Chippewa'
        ],
        [
            'tridiuum_value' => 'b7e79c5e-9d1f-422f-afd0-04a06fa7e2a5',
            'label' => 'Scandinavian'
        ],
        [
            'tridiuum_value' => '1e73133c-cf6d-4333-9175-acd08825fcc6',
            'label' => 'Scotch-Irish'
        ],
        [
            'tridiuum_value' => '3586669e-4a4c-4bed-9563-d6a08030f96b',
            'label' => 'Scottish'
        ],
        [
            'tridiuum_value' => '21fe6a31-ce8c-423c-b17d-8d67a3b0bfe7',
            'label' => 'Seminole'
        ],
        [
            'tridiuum_value' => '845f9974-d5d5-4a21-ab18-542a0aba5195',
            'label' => 'Seneca'
        ],
        [
            'tridiuum_value' => '5fbc2a4d-9c30-45e6-b64d-ac1c2b150248',
            'label' => 'Senegalese'
        ],
        [
            'tridiuum_value' => 'e7ef8a96-72ba-4aa8-b855-953a4edfa7ec',
            'label' => 'Serb/Serbian'
        ],
        [
            'tridiuum_value' => 'c6a38a04-e785-49f8-ba31-e6fec862c7c7',
            'label' => 'Shawnee'
        ],
        [
            'tridiuum_value' => '0272f876-0f15-46b9-b535-d5eb1493fde5',
            'label' => 'Shona'
        ],
        [
            'tridiuum_value' => 'ed37422b-e102-4d89-a991-1267c442d9f7',
            'label' => 'Shoshone'
        ],
        [
            'tridiuum_value' => '4543d6f9-a998-42d3-9d7d-83fc90ca9a09',
            'label' => 'Sierra Leonean'
        ],
        [
            'tridiuum_value' => '0c7daa21-aa07-4b7b-a92d-d22419e50fb9',
            'label' => 'Singaporean/Singapore'
        ],
        [
            'tridiuum_value' => '566f88f6-80df-4aaf-b67a-ae65f0b7859c',
            'label' => 'Sinhalese/Singhalese'
        ],
        [
            'tridiuum_value' => 'eb385eb1-c853-4b30-b800-b61b9b87b690',
            'label' => 'Sioux'
        ],
        [
            'tridiuum_value' => '61a5221e-ab46-4913-b9b3-61c05f89fa36',
            'label' => 'Slavic'
        ],
        [
            'tridiuum_value' => 'f8c69e7f-b616-492c-a770-8f4efca22e21',
            'label' => 'Slovak'
        ],
        [
            'tridiuum_value' => '1f75e410-5e5e-4d14-be5b-4a250a727eab',
            'label' => 'Slovene/Slovenian'
        ],
        [
            'tridiuum_value' => '66f09267-1155-442b-b830-b0a25346755e',
            'label' => 'Somali/Somalian'
        ],
        [
            'tridiuum_value' => 'fe1c1993-7485-4169-9204-e9f8632498c5',
            'label' => 'South African'
        ],
        [
            'tridiuum_value' => '1c3d504e-7a08-4975-b9aa-3d61cc63f8d8',
            'label' => 'South American Indian'
        ],
        [
            'tridiuum_value' => '9bfb2467-42d0-4f14-9b00-81b812cf4986',
            'label' => 'South American (Nos)'
        ],
        [
            'tridiuum_value' => 'f2ad9a59-7d7f-4293-8308-17e468aad463',
            'label' => 'Soviet/Soviet Union'
        ],
        [
            'tridiuum_value' => '1bbc3858-fb1b-47ce-945e-8f48a90caf82',
            'label' => 'Spaniard/Spanish'
        ],
        [
            'tridiuum_value' => '31d05541-6f39-4e0d-801e-a77d38d0640c',
            'label' => 'Spanish American Indian'
        ],
        [
            'tridiuum_value' => '2be6c232-f1df-4c57-84e8-72edea40ac57',
            'label' => 'Sri Lankan'
        ],
        [
            'tridiuum_value' => '3b28ab0c-d0d7-4133-a69f-1b863677db97',
            'label' => 'Sudanese'
        ],
        [
            'tridiuum_value' => 'b56addef-6b60-4aee-abef-27b32ebd43b7',
            'label' => 'Swede/Swedish'
        ],
        [
            'tridiuum_value' => 'd6da9194-8058-40f3-b28f-96f54df98b29',
            'label' => 'Swiss'
        ],
        [
            'tridiuum_value' => 'e0342836-4056-484a-be42-7d0bbb5dd824',
            'label' => 'Syrian'
        ],
        [
            'tridiuum_value' => '7674a920-c042-4d14-aa11-8b26904b3e61',
            'label' => 'Tagalog'
        ],
        [
            'tridiuum_value' => '67306fc4-0d52-40aa-b4d6-fbbcc8a7173b',
            'label' => 'Tahitian'
        ],
        [
            'tridiuum_value' => '92f5de55-75b9-45d4-ad92-20fba6b8525d',
            'label' => 'Taiwanese'
        ],
        [
            'tridiuum_value' => 'd9233c06-4125-4a69-bb43-f2c7d5e38965',
            'label' => 'Tanzanian'
        ],
        [
            'tridiuum_value' => '87397b28-f7c8-4f6d-b60a-3667256779db',
            'label' => 'Temne/Temme/Themne'
        ],
        [
            'tridiuum_value' => '22278cfd-6343-468e-aa95-05c9cfd4550d',
            'label' => 'Teton Sioux'
        ],
        [
            'tridiuum_value' => '76481e15-c086-40ed-916a-11d0397bc035',
            'label' => 'Thai'
        ],
        [
            'tridiuum_value' => 'e11d1294-fc9c-467d-9eb4-75c21fa17eb0',
            'label' => 'Tigrinya/Tigray/Tigraway'
        ],
        [
            'tridiuum_value' => 'afb9f0c3-608f-4258-95aa-b3ded73c7044',
            'label' => 'Tlingit'
        ],
        [
            'tridiuum_value' => '9074a2e4-4b1d-400f-b064-2552f2c0065b',
            'label' => 'Tlingit-Haida'
        ],
        [
            'tridiuum_value' => 'ac3c7102-199b-40bc-9318-70f1a2f69062',
            'label' => 'Togolese/Togo'
        ],
        [
            'tridiuum_value' => '29649467-cfc9-470b-a942-ddc781876513',
            'label' => 'Tohono O\'Odham'
        ],
        [
            'tridiuum_value' => '02eaf39a-c45a-4b99-8f36-d12bc6afa1dc',
            'label' => 'Tokelauan'
        ],
        [
            'tridiuum_value' => 'ac9f7a4f-3571-4758-b6ff-62bd21c1f95b',
            'label' => 'Tongan'
        ],
        [
            'tridiuum_value' => 'fa7f6291-74da-4ccb-8117-75df1c884e69',
            'label' => 'Trinidadian/Tobagonian'
        ],
        [
            'tridiuum_value' => 'f1046254-b358-4644-8b69-2fa8926476e8',
            'label' => 'Tunisian'
        ],
        [
            'tridiuum_value' => 'd68cb000-9c45-49fa-baf4-9c5ee75618f4',
            'label' => 'Turk/Turkish'
        ],
        [
            'tridiuum_value' => '07bc2a62-958d-4e65-9d46-1efbf7587781',
            'label' => 'Turtle Mountain Band/Turtle Mountain'
        ],
        [
            'tridiuum_value' => 'd33d9d3e-e2d1-43e6-b532-0e26839f199c',
            'label' => 'Ugandan'
        ],
        [
            'tridiuum_value' => '903cf97a-7558-42f8-a5e1-960a4280e09c',
            'label' => 'Ukrainian'
        ],
        [
            'tridiuum_value' => 'e8fabe83-9053-49ff-8f26-ff06de38d7af',
            'label' => 'Unknown'
        ],
        [
            'tridiuum_value' => '29f3ee06-561b-47f5-a65b-cf1b4853aac2',
            'label' => 'Unknown/Not Specified'
        ],
        [
            'tridiuum_value' => '77bb1e1b-5cd4-42d4-9279-f1175d548472',
            'label' => 'Uruguayan'
        ],
        [
            'tridiuum_value' => '17ca5d47-44d3-4cee-9069-a7877b307bca',
            'label' => 'U.S. Virgin Islander'
        ],
        [
            'tridiuum_value' => '231e39be-347c-49c5-b0f1-70586b4fe67b',
            'label' => 'Ute'
        ],
        [
            'tridiuum_value' => '76e0fd35-f176-4a32-b039-5321e92b374f',
            'label' => 'Uzbekistani'
        ],
        [
            'tridiuum_value' => '93a16dda-27d4-47ef-b607-e5eb8171ec83',
            'label' => 'Uzbek/Uzbeg'
        ],
        [
            'tridiuum_value' => '46c3ea53-8b5f-4cdb-89ea-590561db47d1',
            'label' => 'Venezuelan'
        ],
        [
            'tridiuum_value' => 'caad2473-fe1f-4509-a4fa-1a194619c0fd',
            'label' => 'Vietnamese'
        ],
        [
            'tridiuum_value' => '592486f7-4d8b-4452-bbd8-52c4df7c647a',
            'label' => 'Welsh'
        ],
        [
            'tridiuum_value' => '8ada4f6f-430c-49fb-90fe-770a23c53992',
            'label' => 'West Indian'
        ],
        [
            'tridiuum_value' => 'd0f5de26-d7f2-47e9-b679-a155ee7d4691',
            'label' => 'White Mountain Apache'
        ],
        [
            'tridiuum_value' => '1bd213ab-1462-4a50-9985-7d480963d36d',
            'label' => 'Yakama'
        ],
        [
            'tridiuum_value' => '2b770ba0-5aaf-442f-b29a-c23814b515e8',
            'label' => 'Yaqui'
        ],
        [
            'tridiuum_value' => 'eb6c415a-bf0e-460e-a042-356ecd2628d8',
            'label' => 'Yemeni'
        ],
        [
            'tridiuum_value' => '6e170dd3-c35d-4cbc-b008-78130f89581c',
            'label' => 'Yoruba'
        ],
        [
            'tridiuum_value' => '5f6c520b-5b7b-4ab7-b32e-22725d3c120a',
            'label' => 'Yugoslavian'
        ],
        [
            'tridiuum_value' => '55e98b6a-6c50-41a3-8f78-7262521f0111',
            'label' => 'Zimbabwean'
        ],
        [
            'tridiuum_value' => '1f22e04b-e7ae-4248-a07d-ed8c8452c76a',
            'label' => 'Zuni'
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TherapistSurveyEthnicity::query()->delete();

        TherapistSurveyEthnicity::query()->insert($this->ethnicities);
    }
}
