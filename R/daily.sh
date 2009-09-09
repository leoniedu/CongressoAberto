source /home/ca/.bash_profile
rm /home/ca/reps/CongressoAberto/data/camara/rollcalls/*.zip
rm /home/ca/reps/CongressoAberto/data/camara/rollcalls/extracted/*
R --vanilla < ~/reps/CongressoAberto/R/daily.R
