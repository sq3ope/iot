#!/bin/bash

if [ "$#" -ne 1 ]; then
    echo "usage: $0 <htdocs_dir>"
    exit 1
fi

htdocs_dir=$1

touch_p() { mkdir -p "$(dirname "$1")" && touch "$1" ; }

touch_p $htdocs_dir/gateway/api/3.0/Auth/SchoolClassInfo
touch_p $htdocs_dir/gateway/api/2.0/Auth/UnitInfo
touch_p $htdocs_dir/gateway/api/3.0/Auth/Subjects
touch_p $htdocs_dir/gateway/api/3.0/Auth/Classrooms
touch_p $htdocs_dir/gateway/api/2.0/Timetables/IndividualLearningPath
touch_p $htdocs_dir/gateway/api/2.0/Timetables/OneToOneLearningPlan
