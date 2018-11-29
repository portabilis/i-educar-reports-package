<?php

use Phinx\Migration\AbstractMigration;

class PhinxToLaravel extends AbstractMigration
{
    /**
     * List of Phinx migrations to Laravel migrations.
     *
     * @var array
     */
    private $list = [
        'AddDistributionOfUniformPerStudentReportMenu' => '2018_09_21_170830_add_distribution_of_uniform_per_student_report_menu',
        'AddLibraryPublishersReportMenu' => '2018_09_20_174045_add_library_publishers_report_menu',
        'AddLibraryLoansReportMenu' => '2018_09_20_183612_add_library_loans_report_menu',
        'AddFrequencyCertificateReportMenu' => '2018_09_21_133837_add_frequency_certificate_report_menu',
        'AddRegistrationCertificateReportMenu' => '2018_09_21_132429_add_registration_certificate_report_menu',
        'AddLibraryDevolutionsReportMenu' => '2018_09_20_192454_add_library_devolutions_report_menu',
        'AddServantsReportMenu' => '2018_09_21_174226_add_servants_report_menu',
        'AddTransportationRoutesReportMenu' => '2018_09_21_184821_add_transportation_routes_report_menu',
        'AddTransferenceCertificateReportMenu' => '2018_09_21_135721_add_transference_certificate_report_menu',
        'AddTeachersAndCoursesTaughtByClassReportMenu' => '2018_09_21_173535_add_teachers_and_courses_taught_by_class_report_menu',
        'AddClassRecordBookMenu' => '2018_09_27_173007_add_class_record_book_menu',
        'AddStudentSheetReportMenu' => '2018_09_21_124724_add_student_sheet_report_menu',
        'AddTeacherReportCardReportMenu' => '2018_09_21_203129_add_teacher_report_card_report_menu',
        'AddAgeDistortionInSerieReportMenu' => '2018_10_16_234638_add_age_distortion_in_serie_report_menu',
        'AddFinalResultReportMenu' => '2018_10_02_224913_add_final_result_report_menu',
        'AddReportCardReportMenu' => '2018_09_21_204155_add_report_card_report_menu',
        'AddStudentsPerClassReportMenu' => '2018_09_20_210437_add_students_per_class_report_menu',
        'AddLibraryWorksReportMenu' => '2018_09_20_180044_add_library_works_report_menu',
        'AddLibraryLoanReceiptReportMenu' => '2018_09_20_194109_add_library_loan_receipt_report_menu',
        'AddConclusionCertificateReportMenu' => '2018_09_21_140514_add_conclusion_certificate_report_menu',
        'AddLibraryClientsReportMenu' => '2018_09_20_182201_add_library_clients_report_menu',
        'AddStudentsMovementReportMenu' => '2018_09_21_194745_add_students_movement_report_menu',
        'AddConferenceEvaluationsFaultsReportMenu' => '2018_09_21_193817_add_conference_evaluations_faults_report_menu',
        'AddClassAverageComparativeReportMenu' => '2018_10_08_224610_add_class_average_comparative_report_menu',
        'AddStudentsWithBenefitsReportMenu' => '2018_09_21_142205_add_students_with_benefits_report_menu',
        'AddTransportationUsersReportMenu' => '2018_09_21_182948_add_transportation_users_report_menu',
        'AddSchoolsReportMenu' => '2018_09_20_204252_add_schools_report_menu',
        'AddStudentDisciplinaryOccurrenceReportMenu' => '2018_09_21_131512_add_student_disciplinary_occurrence_report_menu',
        'AddStudentsWithDisabilitiesReportMenu' => '2018_09_21_164700_add_students_with_disabilities_report_menu',
        'AddVacancyCertificateReportMenu' => '2018_09_28_200522_add_vacancy_certificate_report_menu',
        'AddStudentsPerProjectsReportMenu' => '2018_09_21_163958_add_students_per_projects_report_menu',
        'AddServantSheetReportMenu' => '2018_10_06_012229_add_servant_sheet_report_menu',
        'AddStudentsTransferredAbandonmentReportMenu' => '2018_09_21_172052_add_students_transferred_abandonment_report_menu',
        'AddSchoolHistoryReportMenu' => '2018_09_28_170910_add_school_history_report_menu',
        'AddDriversReportMenu' => '2018_09_21_190139_add_drivers_report_menu',
        'AddLibraryAuthorsReportMenu' => '2018_09_20_160309_add_library_authors_report_menu',
        'AddEducationalProgressAndProceduresReportMenu' => '2018_09_21_201536_add_educational_progress_and_procedures_report_menu',
        'AddStudentsAverageReportMenu' => '2018_09_21_134808_add_students_average_report_menu',
        'AddLibraryDevolutionReceiptReportMenu' => '2018_09_20_195808_add_library_devolution_receipt_report_menu',
        'AddStudentsEntranceAndAllocationReportMenu' => '2018_09_21_133119_add_students_entrance_and_allocation_report_menu',
    ];

    /**
     * Return the name of Laravel migration if exists for Phinx migration.
     *
     * @param string $name
     *
     * @return string|null
     */
    private function ran($name)
    {
        if (array_key_exists($name, $this->list)) {
            return $this->list[$name];
        }

        return null;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->execute(
            '
                CREATE TABLE IF NOT EXISTS public.migrations (
                    id serial NOT NULL,
                    migration varchar(191) NOT NULL,
                    batch int4 NOT NULL,
                    CONSTRAINT migrations_pkey PRIMARY KEY (id)
                );
            '
        );

        $query = $this->fetchRow(
            '
                SELECT EXISTS (
                    SELECT 1
                    FROM information_schema.tables 
                    WHERE table_schema = \'public\'
                    AND table_name = \'phinxlog\'
                );
            '
        );

        if ($query['exists']) {

            $phinx = $this->fetchAll('SELECT * FROM public.phinxlog;');

            $rows = [];

            foreach ($phinx as $migration) {
                if ($name = $this->ran($migration['migration_name'])) {
                    $rows[] = [
                        'migration' => $name,
                        'batch' => 1,
                    ];
                }
            }

            $this->table('migrations')->insert($rows)->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
