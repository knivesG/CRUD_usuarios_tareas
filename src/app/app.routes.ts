import { Routes } from '@angular/router';
import { UserListComponent } from './components/usuario-list/usuario-list.component';
import { UserFormComponent } from './components/usuario-form/usuario-form.component';
import { TaskListComponent } from './components/tareas-list/tareas-list.component';
import { TaskFormComponent } from './components/tareas-form/tareas-form.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';

export const routes: Routes = [
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' },
  { path: 'dashboard', component: DashboardComponent },
  { path: 'usuarios', component: UserListComponent },
  { path: 'usuarios/nuevo', component: UserFormComponent },
  { path: 'usuarios/editar/:id', component: UserFormComponent },
  { path: 'tareas', component: TaskListComponent },
  { path: 'tareas/nueva', component: TaskFormComponent },
  { path: 'tareas/editar/:id', component: TaskFormComponent },
  { path: '**', redirectTo: '/dashboard' }
];