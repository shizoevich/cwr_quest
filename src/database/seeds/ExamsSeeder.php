<?php

use Illuminate\Database\Seeder;

class ExamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    \App\Question::create(['question' => 'When a breach of PHI affects more than 500 individuals:'])->answers()->saveMany([
	    	new \App\Answer(['answer' => 'Must provide notice to the media', 'is_correct' => 0]),
	    	new \App\Answer(['answer' => 'Must provide notice to the individuals', 'is_correct' => 0]),
	    	new \App\Answer(['answer' => 'Must provide notice to the HHS Secretary', 'is_correct' => 0]),
	    	new \App\Answer(['answer' => 'All of the above', 'is_correct' => 1]),
	    ]);
	    \App\Question::create(['question' => 'Subcontractors of a Business Associate are subject to the HIPAA Privacy law'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'True', 'is_correct' => 1]),
		    new \App\Answer(['answer' => 'False', 'is_correct' => 0]),
	    ]);
	    \App\Question::create(['question' => 'Which of the following is an example of a Technical Safeguard?'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'Assigning an employee to be a Security Officer', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Locking rooms with medical files', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Requiring ID badges for employees', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Encrypting PHI when sending it over the Internet', 'is_correct' => 1]),
	    ]);
	    \App\Question::create(['question' => 'Incidental disclosure is:'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'Always acceptable under the Privacy Rule', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Never acceptable under the Privacy Rule', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Acceptable under the Privacy Rule as long as reasonable safeguards have been taken and the minimumnecessary is disclosed.', 'is_correct' => 1]),
		    new \App\Answer(['answer' => 'None of the above', 'is_correct' => 0]),
	    ]);
	    \App\Question::create(['question' => 'What is an example of a Covered Entity?'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'A pharmacist', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'A dentist', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'A health care plan', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'All of the above', 'is_correct' => 1]),
	    ]);
	    \App\Question::create(['question' => 'De­identified Health Information is subject to the Privacy Rule'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'True', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'False', 'is_correct' => 1]),
	    ]);
	    \App\Question::create(['question' => 'Which of the following did not occur as a result of the Omnibus Final Rule?'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'Individual rights were expanded', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Privacy and Security rules were strengthened', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Business Associates were given increased reponsibilities', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Covered Entities were given more time for breach notification', 'is_correct' => 1]),
	    ]);
	    \App\Question::create(['question' => 'The Security Rule applies to all PHI, whether written, oral or electronic.'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'True', 'is_correct' => 1]),
		    new \App\Answer(['answer' => 'False', 'is_correct' => 0]),
	    ]);
	    \App\Question::create(['question' => 'Which of the following is a permitted use of disclosure of Protected Health Information?'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'For marketing', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'For sale of PHI', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'For treatment, payment, or health care operations', 'is_correct' => 1]),
		    new \App\Answer(['answer' => 'For research', 'is_correct' => 0]),
	    ]);
	    \App\Question::create(['question' => 'Which of the following must be included in a Notice of Privacy Practices?'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'The ways PHI must be used and disclosed', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'The right to opt out of fundraising communications', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'The right to be notified for a breach in PHI', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'All of the above', 'is_correct' => 1]),
	    ]);
	    \App\Question::create(['question' => 'How long does a Covered Entity have to provide an individual with a copy of their PHI uponrequest?'])->answers()->saveMany([
		    new \App\Answer(['answer' => '30 days', 'is_correct' => 1]),
		    new \App\Answer(['answer' => '60 days', 'is_correct' => 0]),
		    new \App\Answer(['answer' => '90 days', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'There is no time frame specified', 'is_correct' => 0]),
	    ]);
	    \App\Question::create(['question' => 'An individual has a right to request a change to information in their PHI that he/she believesinaccurate or incomplete'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'True', 'is_correct' => 1]),
		    new \App\Answer(['answer' => 'False', 'is_correct' => 0]),
	    ]);
	    \App\Question::create(['question' => 'When a State Privacy Rule is more stringent, the State law prevails.'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'True', 'is_correct' => 1]),
		    new \App\Answer(['answer' => 'False', 'is_correct' => 0]),
	    ]);
	    \App\Question::create(['question' => 'The Security Rule addresses:'])->answers()->saveMany([
		    new \App\Answer(['answer' => 'Administrative safeguards', 'is_correct' => 1]),
		    new \App\Answer(['answer' => 'Personal safeguards', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Medical safeguards', 'is_correct' => 0]),
		    new \App\Answer(['answer' => 'Community safeguards', 'is_correct' => 0]),
	    ]);
    }
}
