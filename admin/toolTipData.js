var FiltersEnabled = 0; // if your not going to use transitions or filters in any of the tips set this to 0
var spacer="&nbsp; &nbsp; &nbsp; ";

// email notifications to admin
notifyAdminNewMembers0Tip=["", spacer+"No email notifications to admin."];
notifyAdminNewMembers1Tip=["", spacer+"Notify admin only when a new member is waiting for approval."];
notifyAdminNewMembers2Tip=["", spacer+"Notify admin for all new sign-ups."];

// visitorSignup
visitorSignup0Tip=["", spacer+"If this option is selected, visitors will not be able to join this group unless the admin manually moves them to this group from the admin area."];
visitorSignup1Tip=["", spacer+"If this option is selected, visitors can join this group but will not be able to sign in unless the admin approves them from the admin area."];
visitorSignup2Tip=["", spacer+"If this option is selected, visitors can join this group and will be able to sign in instantly with no need for admin approval."];

// tenants table
tenants_addTip=["",spacer+"This option allows all members of the group to add records to the 'Tenants' table. A member who adds a record to the table becomes the 'owner' of that record."];

tenants_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Tenants' table."];
tenants_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Tenants' table."];
tenants_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Tenants' table."];
tenants_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Tenants' table."];

tenants_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Tenants' table."];
tenants_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Tenants' table."];
tenants_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Tenants' table."];
tenants_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Tenants' table, regardless of their owner."];

tenants_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Tenants' table."];
tenants_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Tenants' table."];
tenants_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Tenants' table."];
tenants_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Tenants' table."];

// user_table table
user_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'User Table' table. A member who adds a record to the table becomes the 'owner' of that record."];

user_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'User Table' table."];
user_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'User Table' table."];
user_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'User Table' table."];
user_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'User Table' table."];

user_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'User Table' table."];
user_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'User Table' table."];
user_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'User Table' table."];
user_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'User Table' table, regardless of their owner."];

user_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'User Table' table."];
user_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'User Table' table."];
user_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'User Table' table."];
user_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'User Table' table."];

// suggestion table
suggestion_addTip=["",spacer+"This option allows all members of the group to add records to the 'Suggestions - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

suggestion_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Suggestions - App' table."];
suggestion_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Suggestions - App' table."];
suggestion_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Suggestions - App' table."];
suggestion_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Suggestions - App' table."];

suggestion_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Suggestions - App' table."];
suggestion_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Suggestions - App' table."];
suggestion_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Suggestions - App' table."];
suggestion_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Suggestions - App' table, regardless of their owner."];

suggestion_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Suggestions - App' table."];
suggestion_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Suggestions - App' table."];
suggestion_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Suggestions - App' table."];
suggestion_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Suggestions - App' table."];

// event_table table
event_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Event - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

event_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Event - App' table."];
event_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Event - App' table."];
event_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Event - App' table."];
event_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Event - App' table."];

event_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Event - App' table."];
event_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Event - App' table."];
event_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Event - App' table."];
event_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Event - App' table, regardless of their owner."];

event_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Event - App' table."];
event_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Event - App' table."];
event_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Event - App' table."];
event_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Event - App' table."];

// event_outcomes_expected_table table
event_outcomes_expected_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Outcomes Expected Table' table. A member who adds a record to the table becomes the 'owner' of that record."];

event_outcomes_expected_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Outcomes Expected Table' table."];

event_outcomes_expected_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Outcomes Expected Table' table, regardless of their owner."];

event_outcomes_expected_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Outcomes Expected Table' table."];
event_outcomes_expected_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Outcomes Expected Table' table."];

// event_participants_table table
event_participants_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Participants / Speaker / VIP List - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

event_participants_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Participants / Speaker / VIP List - App' table."];

event_participants_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Participants / Speaker / VIP List - App' table, regardless of their owner."];

event_participants_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Participants / Speaker / VIP List - App' table."];
event_participants_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Participants / Speaker / VIP List - App' table."];

// event_decision_table table
event_decision_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Decision - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

event_decision_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Decision - App' table."];
event_decision_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Decision - App' table."];
event_decision_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Decision - App' table."];
event_decision_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Decision - App' table."];

event_decision_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Decision - App' table."];
event_decision_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Decision - App' table."];
event_decision_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Decision - App' table."];
event_decision_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Decision - App' table, regardless of their owner."];

event_decision_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Decision - App' table."];
event_decision_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Decision - App' table."];
event_decision_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Decision - App' table."];
event_decision_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Decision - App' table."];

// meetings_table table
meetings_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Meetings - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

meetings_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Meetings - App' table."];
meetings_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Meetings - App' table."];
meetings_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Meetings - App' table."];
meetings_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Meetings - App' table."];

meetings_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Meetings - App' table."];
meetings_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Meetings - App' table."];
meetings_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Meetings - App' table."];
meetings_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Meetings - App' table, regardless of their owner."];

meetings_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Meetings - App' table."];
meetings_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Meetings - App' table."];
meetings_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Meetings - App' table."];
meetings_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Meetings - App' table."];

// meetings_agenda_table table
meetings_agenda_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Meeting Agenda - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

meetings_agenda_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Meeting Agenda - App' table."];
meetings_agenda_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Meeting Agenda - App' table."];
meetings_agenda_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Meeting Agenda - App' table."];
meetings_agenda_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Meeting Agenda - App' table."];

meetings_agenda_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Meeting Agenda - App' table."];
meetings_agenda_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Meeting Agenda - App' table."];
meetings_agenda_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Meeting Agenda - App' table."];
meetings_agenda_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Meeting Agenda - App' table, regardless of their owner."];

meetings_agenda_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Meeting Agenda - App' table."];
meetings_agenda_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Meeting Agenda - App' table."];
meetings_agenda_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Meeting Agenda - App' table."];
meetings_agenda_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Meeting Agenda - App' table."];

// meetings_participants_table table
meetings_participants_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Participants / Speaker / VIP List - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

meetings_participants_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Participants / Speaker / VIP List - App' table."];

meetings_participants_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Participants / Speaker / VIP List - App' table, regardless of their owner."];

meetings_participants_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Participants / Speaker / VIP List - App' table."];
meetings_participants_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Participants / Speaker / VIP List - App' table."];

// meetings_decision_table table
meetings_decision_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Meeting Decision - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

meetings_decision_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Meeting Decision - App' table."];
meetings_decision_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Meeting Decision - App' table."];
meetings_decision_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Meeting Decision - App' table."];
meetings_decision_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Meeting Decision - App' table."];

meetings_decision_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Meeting Decision - App' table."];
meetings_decision_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Meeting Decision - App' table."];
meetings_decision_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Meeting Decision - App' table."];
meetings_decision_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Meeting Decision - App' table, regardless of their owner."];

meetings_decision_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Meeting Decision - App' table."];
meetings_decision_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Meeting Decision - App' table."];
meetings_decision_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Meeting Decision - App' table."];
meetings_decision_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Meeting Decision - App' table."];

// visiting_card_table table
visiting_card_table_addTip=["",spacer+"This option allows all members of the group to add records to the 'Visiting card - App' table. A member who adds a record to the table becomes the 'owner' of that record."];

visiting_card_table_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Visiting card - App' table."];
visiting_card_table_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Visiting card - App' table."];
visiting_card_table_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Visiting card - App' table."];
visiting_card_table_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Visiting card - App' table."];

visiting_card_table_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Visiting card - App' table."];
visiting_card_table_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Visiting card - App' table."];
visiting_card_table_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Visiting card - App' table."];
visiting_card_table_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Visiting card - App' table, regardless of their owner."];

visiting_card_table_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Visiting card - App' table."];
visiting_card_table_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Visiting card - App' table."];
visiting_card_table_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Visiting card - App' table."];
visiting_card_table_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Visiting card - App' table."];

/*
	Style syntax:
	-------------
	[TitleColor,TextColor,TitleBgColor,TextBgColor,TitleBgImag,TextBgImag,TitleTextAlign,
	TextTextAlign,TitleFontFace,TextFontFace, TipPosition, StickyStyle, TitleFontSize,
	TextFontSize, Width, Height, BorderSize, PadTextArea, CoordinateX , CoordinateY,
	TransitionNumber, TransitionDuration, TransparencyLevel ,ShadowType, ShadowColor]

*/

toolTipStyle=["white","#00008B","#000099","#E6E6FA","","images/helpBg.gif","","","","\"Trebuchet MS\", sans-serif","","","","3",400,"",1,2,10,10,51,1,0,"",""];

applyCssFilter();
