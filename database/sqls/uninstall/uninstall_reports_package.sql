-- ============================================================
-- SCRIPT DE REMOÇÃO DO PACOTE i-educar-reports-package
-- ============================================================

-- ============================================================
-- PARTE 0: REMOÇÃO DE REFERÊNCIAS EM menu_tipo_usuario
-- ============================================================

-- Remover permissões dos menus que serão deletados (baseado no campo OLD)
DELETE FROM pmieducar.menu_tipo_usuario
WHERE menu_id IN (
    SELECT id FROM menus
    WHERE old IN (
        999828, 999881, 999806, 9998911, 999609, 999602, 999218, 999204,
        999886, 999810, 999883, 999108, 999235, 999231, 999870, 999105,
        999859, 999107, 999223, 999914, 999916, 999913, 999500, 999460,
        999861, 999925, 999450, 999400, 999303, 999923, 999300, 999922,
        999301, 21127, 21126
    )
);

-- Remover permissões dos menus que serão deletados (baseado no campo PROCESS)
DELETE FROM pmieducar.menu_tipo_usuario
WHERE menu_id IN (
    SELECT id FROM menus
    WHERE process IN (
        999244, 999882, 9998862, 9998868, 999100, 999216, 999860, 999205,
        999227, 999233, 999607, 999234, 999101, 999201, 999203, 999871,
        999834, 999217, 999820, 999822, 999605, 999200, 999202, 999103,
        999102, 999608, 999830, 999224, 999809, 999812, 999816, 999872,
        9998911, 999840
    )
);

-- ============================================================
-- PARTE 1: REMOÇÃO DE MENUS (Ordem reversa - 2024 até 2020)
-- ============================================================

-- 2024: Menus mais recentes (3 menus - usando WHERE old)
DELETE FROM menus WHERE old = 999828;  -- audit_evaluations_faults_report_menu
DELETE FROM menus WHERE old = 999881;  -- report_card_transference_menu
DELETE FROM menus WHERE old = 999806;  -- abandonment_certificate_report_menu

-- ============================================================
-- 2023: Functions (1 function)
-- ============================================================
DROP FUNCTION IF EXISTS relatorio.retorna_situacao_matricula_componente(numeric, numeric);

-- ============================================================
-- 2022: Menus e objetos de banco (22 items)
-- ============================================================

-- Menus usando WHERE old (16 menus)
DELETE FROM menus WHERE old = 9998911; -- minutes_final_result_report_menu
DELETE FROM menus WHERE old = 999609;  -- class_board_map_report_menu
DELETE FROM menus WHERE old = 999602;  -- student_card_report_menu
DELETE FROM menus WHERE old = 999218;  -- enrollment_quantitative_map_report_menu
DELETE FROM menus WHERE old = 999204;  -- white_student_form_report_menu
DELETE FROM menus WHERE old = 999886;  -- final_map_by_discipline_report_menu
DELETE FROM menus WHERE old = 999810;  -- schooling_certificate_report_menu
DELETE FROM menus WHERE old = 999883;  -- pending_students_report_menu
DELETE FROM menus WHERE old = 999108;  -- not_enrollment_report_menu
DELETE FROM menus WHERE old = 999235;  -- tags_report_menu
DELETE FROM menus WHERE old = 999231;  -- score_absence_release_report_menu
DELETE FROM menus WHERE old = 999870;  -- parent_signature_report_menu
DELETE FROM menus WHERE old = 999105;  -- registration_school_report_menu
DELETE FROM menus WHERE old = 999859;  -- teachers_per_school_class_report_menu
DELETE FROM menus WHERE old = 999107;  -- employee_allocated_time_report_menu
DELETE FROM menus WHERE old = 999223;  -- user_access_report_menu

-- Menus usando WHERE process (4 exceções em 2022)
DELETE FROM menus WHERE process = 999244;  -- user_access_graphic_report_menu
DELETE FROM menus WHERE process = 999882;  -- final_situation_report_menu
DELETE FROM menus WHERE process = 9998862; -- monthly_movement_report_menu
DELETE FROM menus WHERE process = 9998868; -- general_movement_report_menu

-- Function
DROP FUNCTION IF EXISTS relatorio.media_anual_modelo1(integer, integer);

-- View
DROP VIEW IF EXISTS relatorio.view_historico_eja;

-- ============================================================
-- 2020: View e menus (32 items)
-- ============================================================

-- NOTA: relatorio.view_dados_escola NÃO é removida aqui porque é gerenciada pelo i-Educar Core
-- (database/migrations/report/2020_08_27_111536_create_view_dados_escola.php)

-- Menus 2020 (30 menus - todos usando WHERE process)
DELETE FROM menus WHERE process = 999100;  -- vacancy_certificate_report_menu
DELETE FROM menus WHERE process = 999216;  -- transference_certificate_report_menu
DELETE FROM menus WHERE process = 999860;  -- teachers_and_courses_taught_by_class_report_menu
DELETE FROM menus WHERE process = 999205;  -- teacher_report_card_report_menu
DELETE FROM menus WHERE process = 999227;  -- students_with_disabilities_report_menu
DELETE FROM menus WHERE process = 999233;  -- students_with_benefits_report_menu
DELETE FROM menus WHERE process = 999607;  -- students_transferred_abandonment_report_menu
DELETE FROM menus WHERE process = 999234;  -- students_per_projects_report_menu
DELETE FROM menus WHERE process = 999101;  -- students_per_class_report_menu
DELETE FROM menus WHERE process = 999201;  -- students_movement_report_menu
DELETE FROM menus WHERE process = 999203;  -- student_sheet_report_menu
DELETE FROM menus WHERE process = 999871;  -- students_entrance_and_allocation_report_menu
DELETE FROM menus WHERE process = 999834;  -- students_average_report_menu
DELETE FROM menus WHERE process = 999217;  -- student_disciplinary_occurrence_report_menu
DELETE FROM menus WHERE process = 999820;  -- servants_report_menu
DELETE FROM menus WHERE process = 999822;  -- servant_sheet_report_menu
DELETE FROM menus WHERE process = 999605;  -- schools_report_menu
DELETE FROM menus WHERE process = 999200;  -- school_history_report_menu
DELETE FROM menus WHERE process = 999202;  -- report_card_report_menu
DELETE FROM menus WHERE process = 999103;  -- registration_certificate_report_menu
DELETE FROM menus WHERE process = 999102;  -- frequency_certificate_report_menu
DELETE FROM menus WHERE process = 999608;  -- final_result_report_menu
DELETE FROM menus WHERE process = 999830;  -- educational_progress_and_procedures_report_menu
DELETE FROM menus WHERE process = 999224;  -- distribution_of_uniform_per_student_report_menu
DELETE FROM menus WHERE process = 999809;  -- conference_evaluations_faults_report_menu
DELETE FROM menus WHERE process = 999812;  -- conclusion_certificate_report_menu
DELETE FROM menus WHERE process = 999816;  -- class_record_book_menu
DELETE FROM menus WHERE process = 999872;  -- class_average_comparative_report_menu
DELETE FROM menus WHERE process = 9998911; -- birthdays_report_menu
DELETE FROM menus WHERE process = 999840;  -- age_distortion_in_serie_report_menu

-- Base menus 2020 (16 menus estruturais - usando WHERE old)
DELETE FROM menus WHERE old = 999914;
DELETE FROM menus WHERE old = 999916;
DELETE FROM menus WHERE old = 999913;
DELETE FROM menus WHERE old = 999500;
DELETE FROM menus WHERE old = 999460;
DELETE FROM menus WHERE old = 999861;
DELETE FROM menus WHERE old = 999925;
DELETE FROM menus WHERE old = 999450;
DELETE FROM menus WHERE old = 999400;
DELETE FROM menus WHERE old = 999303;
DELETE FROM menus WHERE old = 999923;
DELETE FROM menus WHERE old = 999300;
DELETE FROM menus WHERE old = 999922;
DELETE FROM menus WHERE old = 999301;
DELETE FROM menus WHERE old = 21127;
DELETE FROM menus WHERE old = 21126;

-- ============================================================
-- PARTE 2: LIMPAR REGISTROS DE MIGRATIONS
-- Lista EXATA dos 58 arquivos do pacote (sem path, sem .php)
-- ============================================================

DELETE FROM public.migrations
WHERE migration IN (
    '2020_01_01_191000_base_menus',
    '2020_01_01_192000_add_age_distortion_in_serie_report_menu',
    '2020_01_01_192000_add_birthdays_report_menu',
    '2020_01_01_192000_add_class_average_comparative_report_menu',
    '2020_01_01_192000_add_class_record_book_menu',
    '2020_01_01_192000_add_conclusion_certificate_report_menu',
    '2020_01_01_192000_add_conference_evaluations_faults_report_menu',
    '2020_01_01_192000_add_distribution_of_uniform_per_student_report_menu',
    '2020_01_01_192000_add_educational_progress_and_procedures_report_menu',
    '2020_01_01_192000_add_final_result_report_menu',
    '2020_01_01_192000_add_frequency_certificate_report_menu',
    '2020_01_01_192000_add_registration_certificate_report_menu',
    '2020_01_01_192000_add_report_card_report_menu',
    '2020_01_01_192000_add_school_history_report_menu',
    '2020_01_01_192000_add_schools_report_menu',
    '2020_01_01_192000_add_servant_sheet_report_menu',
    '2020_01_01_192000_add_servants_report_menu',
    '2020_01_01_192000_add_student_disciplinary_occurrence_report_menu',
    '2020_01_01_192000_add_students_average_report_menu',
    '2020_01_01_192000_add_students_entrance_and_allocation_report_menu',
    '2020_01_01_192000_add_student_sheet_report_menu',
    '2020_01_01_192000_add_students_movement_report_menu',
    '2020_01_01_192000_add_students_per_class_report_menu',
    '2020_01_01_192000_add_students_per_projects_report_menu',
    '2020_01_01_192000_add_students_transferred_abandonment_report_menu',
    '2020_01_01_192000_add_students_with_benefits_report_menu',
    '2020_01_01_192000_add_students_with_disabilities_report_menu',
    '2020_01_01_192000_add_teacher_report_card_report_menu',
    '2020_01_01_192000_add_teachers_and_courses_taught_by_class_report_menu',
    '2020_01_01_192000_add_transference_certificate_report_menu',
    '2020_01_01_192000_add_vacancy_certificate_report_menu',
    '2020_06_19_152923_recreate_view_dados_escola',
    '2022_10_18_103500_add_general_movement_report_menu',
    '2022_10_19_090723_add_monthly_movement_report_menu',
    '2022_10_19_152923_add_final_situation_report_menu',
    '2022_11_06_192000_add_report_view_historic_eja',
    '2022_11_08_152925_add_user_access_report_menu',
    '2022_11_08_153924_add_user_access_graphic_report_menu',
    '2022_11_08_153955_add_employee_allocated_time_report_menu',
    '2022_11_08_154955_add_teachers_per_school_class_report_menu',
    '2022_11_09_114955_add_registration_school_report_menu',
    '2022_11_09_114964_add_parent_signature_report_menu',
    '2022_11_09_114975_add_ score_absence_release_report_menu',
    '2022_11_17_152925_add_tags_report_menu',
    '2022_11_17_152926_add_not_enrollment_report_menu',
    '2022_11_17_152927_add_pending_students_report_menu',
    '2022_11_17_152929_add_schooling_certificate_report_menu',
    '2022_11_18_152920_add_final_map_by_discipline_report_menu',
    '2022_11_18_152921_add_function_report_annual_average_model1',
    '2022_11_20_152920_add_white_student_form_report_menu',
    '2022_11_20_152921_add_enrollment_quantitative_map_report_menu',
    '2022_11_21_152921_add_student_card_report_menu',
    '2022_11_21_152922_add_class_board_map_report_menu',
    '2022_11_21_152925_add_minutes_final_result_report_menu',
    '2023_02_08_130000_add_function_report_discipline_registration_situation',
    '2024_06_19_152929_add_abandonment_certificate_report_menu',
    '2024_06_19_184735_add_report_card_transference_menu',
    '2024_06_20_132925_add_audit_evaluations_faults_report_menu'
);

-- ============================================================
-- PARTE 3: REMOÇÃO DE FUNCTIONS LEGADAS
-- Estas functions existem em instalações antigas mas não fazem
-- parte do pacote novo (i-educar-reports)
-- ============================================================

DROP FUNCTION IF EXISTS relatorio.formata_nome CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_media_recuperacao_semestral CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_qtde_alunos_situacao(integer) CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_qtde_alunos_situacao(integer, integer) CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_qtde_etapa_disciplina_dispensada_matricula CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_telefone_pessoa CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_texto_sem_espaco CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_ultima_matricula_turma CASCADE;
DROP FUNCTION IF EXISTS relatorio.get_ultima_observacao_historico CASCADE;
DROP FUNCTION IF EXISTS relatorio.historico_carga_horaria_componente CASCADE;