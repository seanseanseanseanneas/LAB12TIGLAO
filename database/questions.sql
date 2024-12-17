CREATE TABLE questions (
    id SERIAL PRIMARY KEY,
    item_number INT NOT NULL,
    question TEXT NOT NULL,
    choices JSON NOT NULL,
    correct_answer CHAR(1) NOT NULL CHECK (correct_answer IN ('A', 'B', 'C', 'D'))
);

INSERT INTO questions (item_number, question, choices, correct_answer) VALUES
(1, 'What is the main goal of Integrative Programming and Technologies (IPT)?',
 '[{"letter": "A", "choice": "Streamlining programming processes"}, {"letter": "B", "choice": "Unifying various coding languages and tools"}, {"letter": "C", "choice": "Focusing solely on mobile app development"}, {"letter": "D", "choice": "Enhancing data visualization techniques"}]',
 'B'),
(2, 'What is a significant advantage of using IPT?',
 '[{"letter": "A", "choice": "Improved collaboration among teams"}, {"letter": "B", "choice": "Greater innovation through diverse technologies"}, {"letter": "C", "choice": "Cost savings in development"}, {"letter": "D", "choice": "All of the above"}]',
 'D'),
(3, 'Which programming approach is frequently incorporated in IPT?',
 '[{"letter": "A", "choice": "Event-driven programming"}, {"letter": "B", "choice": "Declarative programming"}, {"letter": "C", "choice": "Modular programming"}, {"letter": "D", "choice": "All of the above"}]',
 'D'),
(4, 'What function do web services serve in Integrative Programming and Technologies?',
 '[{"letter": "A", "choice": "They improve code readability"}, {"letter": "B", "choice": "They facilitate data exchange between applications"}, {"letter": "C", "choice": "They optimize user authentication"}, {"letter": "D", "choice": "They manage application deployment"}]',
 'B'),
(5, 'Which framework is commonly utilized in IPT for enhancing application performance?',
 '[{"letter": "A", "choice": "Microservices architecture"}, {"letter": "B", "choice": "Static site generators"}, {"letter": "C", "choice": "Content management systems"}, {"letter": "D", "choice": "Traditional monolithic architecture"}]',
 'A');