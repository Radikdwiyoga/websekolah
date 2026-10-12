<?php

namespace Tests\Feature\Domain;

use App\Enums\AnnouncementTarget as AnnouncementTargetEnum;
use App\Enums\AssignmentStatus;
use App\Enums\AttendanceStatus;
use App\Enums\SettingType;
use App\Enums\SubmissionStatus;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\AnnouncementTarget;
use App\Models\ApplicationSetting;
use App\Models\Assignment;
use App\Models\AssignmentAnswer;
use App\Models\AssignmentQuestion;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SystemLog;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_is_linked_to_user_class_and_guardians(): void
    {
        $class = SchoolClass::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);
        $guardian = Guardian::factory()->create();

        $guardian->students()->attach($student->id, [
            'relation' => 'Ibu',
            'is_primary' => true,
        ]);

        $this->assertTrue($student->user->isStudent());
        $this->assertTrue($student->schoolClass->is($class));
        $this->assertTrue($student->guardians->first()->is($guardian));
        $this->assertTrue((bool) $student->guardians->first()->pivot->is_primary);
        $this->assertTrue($guardian->user->isParent());
        $this->assertTrue($guardian->students->first()->is($student));
    }

    public function test_user_exposes_profile_relations_for_each_role(): void
    {
        $student = Student::factory()->create();
        $teacher = Teacher::factory()->create();
        $guardian = Guardian::factory()->create();

        $this->assertTrue($student->user->student->is($student));
        $this->assertTrue($teacher->user->teacher->is($teacher));
        $this->assertTrue($guardian->user->guardian->is($guardian));
    }

    public function test_school_year_and_class_relationships(): void
    {
        $schoolYear = SchoolYear::factory()->active()->create();
        $teacher = Teacher::factory()->create();
        $class = SchoolClass::factory()->create([
            'school_year_id' => $schoolYear->id,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $this->assertTrue($schoolYear->classes->first()->is($class));
        $this->assertTrue($class->schoolYear->is($schoolYear));
        $this->assertTrue($class->homeroomTeacher->is($teacher));
        $this->assertTrue($teacher->homeroomClasses->first()->is($class));
    }

    public function test_teaching_assignment_links_teacher_class_subject_and_year(): void
    {
        $assignment = TeachingAssignment::factory()->create();

        $this->assertTrue($assignment->teacher->is(Teacher::find($assignment->teacher_id)));
        $this->assertTrue($assignment->schoolClass->is(SchoolClass::find($assignment->class_id)));
        $this->assertTrue($assignment->subject->is(Subject::find($assignment->subject_id)));
        $this->assertTrue($assignment->schoolYear->is(SchoolYear::find($assignment->school_year_id)));
    }

    public function test_assignment_question_submission_answer_and_grade_relationships(): void
    {
        $assignment = Assignment::factory()->published()->create();
        $question = AssignmentQuestion::factory()->create(['assignment_id' => $assignment->id]);
        $submission = AssignmentSubmission::factory()->graded(90)->create(['assignment_id' => $assignment->id]);
        $answer = AssignmentAnswer::factory()->create([
            'assignment_submission_id' => $submission->id,
            'assignment_question_id' => $question->id,
            'is_correct' => true,
            'score' => 1,
        ]);
        $grade = Grade::factory()->create([
            'student_id' => $submission->student_id,
            'subject_id' => $assignment->subject_id,
            'assignment_id' => $assignment->id,
        ]);

        $this->assertSame(AssignmentStatus::Published, $assignment->status);
        $this->assertSame(SubmissionStatus::Graded, $submission->status);
        $this->assertSame('90.00', (string) $submission->teacher_score);
        $this->assertTrue($assignment->questions->first()->is($question));
        $this->assertTrue($question->assignment->is($assignment));
        $this->assertTrue($submission->assignment->is($assignment));
        $this->assertTrue($answer->submission->is($submission));
        $this->assertTrue($answer->question->is($question));
        $this->assertTrue($assignment->submissions->first()->is($submission));
        $this->assertTrue($grade->assignment->is($assignment));
        $this->assertTrue($assignment->grades->first()->is($grade));
    }

    public function test_attendance_session_and_record_relationships(): void
    {
        $session = AttendanceSession::factory()->create();
        $record = AttendanceRecord::factory()->create(['attendance_session_id' => $session->id]);

        $this->assertTrue($session->isOpen());
        $this->assertTrue($session->records->first()->is($record));
        $this->assertTrue($record->attendanceSession->is($session));
        $this->assertSame(AttendanceStatus::BelumAbsen, $record->status);
        $this->assertTrue($record->student->is(Student::find($record->student_id)));
    }

    public function test_announcement_target_relation_uses_the_target_model(): void
    {
        $announcement = Announcement::factory()->published()->create([
            'target_type' => AnnouncementTargetEnum::ClassRoom,
        ]);
        $target = AnnouncementTarget::factory()->forClass()->create([
            'announcement_id' => $announcement->id,
        ]);

        $this->assertTrue($announcement->isPublished());
        $this->assertTrue($announcement->targets->first()->is($target));
        $this->assertSame(AnnouncementTargetEnum::ClassRoom, $target->target_type);
        $this->assertTrue($target->schoolClass->is(SchoolClass::find($target->class_id)));
    }

    public function test_announcement_reads_and_system_logs_belong_to_user(): void
    {
        $user = User::factory()->asStudent()->create();
        $read = AnnouncementRead::factory()->create(['user_id' => $user->id]);
        $log = SystemLog::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->announcementReads->first()->is($read));
        $this->assertTrue($read->announcement->is(Announcement::find($read->announcement_id)));
        $this->assertTrue($user->systemLogs->first()->is($log));
        $this->assertTrue($log->user->is($user));
    }

    public function test_application_setting_decodes_typed_values(): void
    {
        $integer = ApplicationSetting::factory()->create([
            'key' => 'example_integer',
            'value' => '100',
            'type' => SettingType::Integer,
        ]);
        $boolean = ApplicationSetting::factory()->create([
            'key' => 'example_boolean',
            'value' => '0',
            'type' => SettingType::Boolean,
        ]);
        $json = ApplicationSetting::factory()->create([
            'key' => 'example_json',
            'value' => '["a","b"]',
            'type' => SettingType::Json,
        ]);

        $this->assertSame(100, $integer->typedValue());
        $this->assertFalse($boolean->typedValue());
        $this->assertSame(['a', 'b'], $json->typedValue());
    }
}
