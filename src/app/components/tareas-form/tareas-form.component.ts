import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute, RouterModule } from '@angular/router';
import { TareaService } from '../../services/tarea.service';
import { UsuarioService } from '../../services/usuario.service';
import { Tarea } from '../../models/tarea.model';
import { Usuario } from '../../models/usuario.model';

@Component({
  selector: 'app-task-form',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './tareas-form.html',
  styleUrls: ['./tareas-form.css'],
})
export class TaskFormComponent implements OnInit {
  task: Tarea = {
    id: null,
    titulo: '',
    descripcion: '',
    estado: 'pendiente',
    entrega_fecha: '',
    usuario_id: 0,
    usuario_nombre: '',
  };
  
  users: Usuario[] = [];
  isEditMode = false;
  loading = false;
  error = '';

  constructor(
    private taskService: TareaService,
    private userService: UsuarioService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit() {
    this.loadUsers();
    
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.isEditMode = true;
      this.loadTask(parseInt(id));
    }
  }

  loadUsers() {
    this.userService.getUsers().subscribe({
      next: (users) => {
        this.users = users;
      },
      error: (error) => {
        console.error('Error al cargar usuarios:', error);
      }
    });
  }

  loadTask(id: number) {
    this.taskService.getTask(id).subscribe({
      next: (task) => {
        this.task = task;
        // Formatear fecha para el input date
        if (this.task.entrega_fecha) {
          const date = new Date(this.task.entrega_fecha);
          this.task.entrega_fecha = date.toISOString().split('T')[0];
        }
      },
      error: (error) => {
        this.error = 'Error al cargar la tarea';
        console.error('Error:', error);
      }
    });
  }

  onSubmit() {
    this.loading = true;
    this.error = '';

    // Preparar datos para envío
    const taskData = { ...this.task };
    
    // Si no hay usuario seleccionado, establecer como null
    if (!taskData.usuario_id) {
      taskData.usuario_id = null;
    }

    const request = this.isEditMode
      ? this.taskService.updateTask(this.task.id!, taskData)
      : this.taskService.createTask(taskData);

    request.subscribe({
      next: () => {
        this.router.navigate(['/tareas']);
      },
      error: (error) => {
        this.error = this.isEditMode 
          ? 'Error al actualizar la tarea'
          : 'Error al crear la tarea';
        this.loading = false;
        console.error('Error:', error);
      }
    });
  }
}