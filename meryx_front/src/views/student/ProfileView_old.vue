<!-- <template>

<div class="profile-page">


<!-- ================= HEADER COVER ================= ->


<section class="profile-cover">


    <div class="cover-overlay"></div>


</section>





<!-- ================= PROFILE HEADER ================= ->


<section class="profile-card">


    <div class="avatar">

        <!-- <img
        src="https://i.pravatar.cc/200"
        alt="student"
        /> ->
        <img src="https://i.pravatar.cc/200?img=11" class="avatar" />
    </div>



    <div class="student-info">


        <h1>
            Amadou Coulibaly
        </h1>


        <h3>
            Mining Engineering Student
        </h3>


        <p>

        <i class="bi bi-building"></i>

        EUTG University

        </p>



        <p>

        <i class="bi bi-geo-alt"></i>

        Bamako, Mali

        </p>



    </div>




    <div class="profile-actions">


        <button class="edit">

            Edit Profile

        </button>



        <button class="download">

            Download CV

        </button>


    </div>



</section>








<!-- ================= CONTENT ================= ->


<div class="profile-container">



<!-- LEFT SIDE ->


<div class="left-column">



<section class="box">


<h2>
About Me
</h2>


<p>

Passionate mining engineering student interested in
geology, mineral exploration and digital transformation
of mining industries.

</p>


</section>





<section class="box">


<h2>
Academic Information
</h2>



<div class="item">

<strong>
University
</strong>

<span>
EUTG University
</span>


</div>



<div class="item">

<strong>
Degree
</strong>

<span>
Bachelor Computer Science / Mining Technology
</span>


</div>



<div class="item">

<strong>
Level
</strong>

<span>
Third Year
</span>


</div>



<div class="item">

<strong>
GPA
</strong>

<span class="score">
3.7 / 4
</span>


</div>



</section>








<section class="box">


<h2>
Skills
</h2>



<div class="skills">


<span
v-for="skill in skills"
:key="skill"
>

{{skill}}

</span>


</div>


</section>







<section class="box">


<h2>
Projects
</h2>



<div
class="project"
v-for="project in projects"
:key="project.title"
>


<h3>
{{project.title}}
</h3>


<p>
{{project.description}}
</p>


</div>



</section>





</div>









<!-- RIGHT SIDE ->


<div class="right-column">





<section class="box match">


<h2>
MeryX Opportunity Match
</h2>



<div class="circle">

92%

</div>



<p>

Your profile matches with
<strong>
25 opportunities
</strong>

</p>



</section>








<section class="box">


<h2>
Experience
</h2>



<div
class="timeline"
v-for="exp in experiences"
:key="exp.title"
>


<h3>
{{exp.title}}
</h3>


<p>
{{exp.company}}
</p>


<span>
{{exp.date}}
</span>


</div>



</section>









<section class="box">


<h2>
Certificates
</h2>



<ul>

<li
v-for="cert in certificates"
:key="cert"
>

{{cert}}

</li>


</ul>



</section>









<section class="box">


<h2>
Documents
</h2>



<div class="document">


<i class="bi bi-file-earmark-pdf"></i>


CV_Amadou.pdf



<button>
View
</button>


</div>





<div class="document">


<i class="bi bi-file-earmark-text"></i>


Academic_Transcript.pdf



<button>
View
</button>


</div>



</section>






</div>



</div>



</div>

</template> -->









<!-- <script setup>



const skills=[

"Geology",
"Mining Exploration",
"Python",
"Data Analysis",
"AutoCAD",
"GIS",
"Machine Learning"

]




const projects=[

{

title:"Mineral Mapping Platform",

description:
"AI based platform for analyzing geological data."

},


{

title:"Mining Safety Application",

description:
"Digital solution improving worker safety."

}

]




const experiences=[


{

title:"IT Intern",

company:"New Technology Africa",

date:"2026"

},


{

title:"Research Assistant",

company:"University Mining Lab",

date:"2025"

}


]



const certificates=[


"Introduction to Mining Engineering",

"Python Data Analysis",

"GIS Fundamentals"

]



</script> -->









<!-- <style scoped> -->

/*
.profile-page{

background:#f5fafc;

min-height:100vh;

width: 200%;
color: #777;

font-family:Arial,sans-serif;

}







/* COVER */


.profile-cover{

height:260px;

background:
linear-gradient(
135deg,
#0D2B45,
#0d2b45
);


}







/* HEADER */


.profile-card{


background:white;

width:85%;

margin:-80px auto 0;

border-radius:25px;

padding:30px;

display:flex;

align-items:center;

gap:30px;

position:relative;

box-shadow:0 20px 40px #0002;


}





.avatar img{

width:150px;

height:150px;

border-radius:50%;

border:6px solid white;

object-fit:cover;

}




.student-info{

flex:1;

}



.student-info h1{

color:#0D2B45;

}





.profile-actions button{


padding:12px 20px;

border-radius:10px;

border:none;

margin:5px;

cursor:pointer;


}



.edit{

background:#D4A017;

color:white;

}



.download{

background:#0d2b45;

color:white;

}







/* CONTENT */


.profile-container{


width:85%;

margin:40px auto;

display:grid;

grid-template-columns:2fr 1fr;

gap:30px;


}





.box{


background:white;

padding:25px;

border-radius:20px;

margin-bottom:25px;

box-shadow:
0 10px 25px #0001;


}





.box h2{


color:#0d2b45;

margin-bottom:20px;


}





.item{


display:flex;

justify-content:space-between;

padding:12px 0;

border-bottom:1px solid #eee;


}






.skills span{


display:inline-block;

background:#e5f8fb;

color:#0d2b45;

padding:8px 15px;

border-radius:20px;

margin:5px;


}






.project{


border-left:4px solid #D4A017;

padding-left:15px;

margin-bottom:20px;


}






.match{


text-align:center;

}



.circle{


width:120px;

height:120px;

border-radius:50%;

margin:auto;

display:flex;

align-items:center;

justify-content:center;

background:#D4A017;

color:white;

font-size:30px;

font-weight:bold;


}





.timeline{


padding:15px;

border-left:3px solid #D4A017;

margin-bottom:20px;


}





.document{


display:flex;

align-items:center;

justify-content:space-between;

background:#f5fafc;

padding:15px;

border-radius:10px;

margin-bottom:10px;


}




.document i{

font-size:25px;

color:#D4A017;

}



.document button{


background:#0D2B45;

color:white;

border:none;

padding:7px 15px;

border-radius:8px;


}









@media(max-width:900px){


.profile-card{

flex-direction:column;

text-align:center;

}


.profile-container{

grid-template-columns:1fr;

}


}


 */

<!-- </style> -->
